<?php

namespace App\Http\Controllers;

use App\Contracts\AnswerGenerator;
use App\Jobs\ProcessDocument;
use App\Models\Answer;
use App\Models\DailyAnswerUsage;
use App\Models\KnowledgeDocument;
use App\Services\DocumentSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SandboxController extends Controller
{
    private const SAMPLE_NAME = 'starter-renewal-policy.txt';

    private const SAMPLE_TEXT = <<<'TEXT'
Renewal process: A renewal request must be reviewed by the account owner and the finance approver before the contract renewal date. Both approvals must be recorded in the renewal log. The account owner confirms the customer requirements and proposed scope. The finance approver confirms pricing and billing terms. If either approval is missing, the renewal must pause until the required review is complete.
TEXT;

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->planAccess && $request->user()->planAccess->plan !== 'sandbox') {
            return redirect()->route('dashboard');
        }

        $userId = $request->user()->id;

        $limit = (int) config('plans.sandbox.daily_question_limit');
        $usedToday = (int) (DailyAnswerUsage::where('user_id', $userId)->whereDate('usage_date', today())->value('answer_count') ?? 0);

        return view('sandbox', [
            'access' => $request->user()->planAccess,
            'documentCount' => KnowledgeDocument::where('user_id', $userId)->count(),
            'readyCount' => KnowledgeDocument::where('user_id', $userId)->where('status', 'ready')->count(),
            'answerCount' => Answer::where('user_id', $userId)->count(),
            'hasSample' => KnowledgeDocument::where('user_id', $userId)->where('name', self::SAMPLE_NAME)->exists(),
            'recentAnswers' => Answer::where('user_id', $userId)->with('citations.chunk.document')->latest()->limit(5)->get(),
            'questionLimit' => $limit,
            'usedToday' => $usedToday,
            'remainingToday' => max(0, $limit - $usedToday),
        ]);
    }

    public function ask(Request $request, DocumentSearch $search, AnswerGenerator $generator): RedirectResponse
    {
        $request->merge(['question' => trim((string) $request->input('question'))]);
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $user = $request->user();
        $limit = (int) config('plans.sandbox.daily_question_limit');
        $used = (int) (DailyAnswerUsage::where('user_id', $user->id)->whereDate('usage_date', today())->value('answer_count') ?? 0);

        if ($used >= $limit) {
            return back()->withInput()->withErrors(['question' => "You have reached today's Sandbox limit of $limit saved answers."]);
        }

        if (! KnowledgeDocument::where('user_id', $user->id)->where('status', 'ready')->exists()) {
            return back()->withInput()->withErrors(['question' => 'Add the starter source before asking a question.']);
        }

        $question = $validated['question'];
        $chunks = $search->search($user->id, $question);
        if ($chunks->isEmpty()) {
            $result = ['answer' => 'I could not find enough evidence in your Sandbox sources to answer that question.', 'citation_ids' => []];
        } else {
            if (blank(config('ai.providers.groq.key'))) {
                return back()->withInput()->withErrors(['question' => 'Answers are unavailable until the answer service is configured.']);
            }

            try {
                $result = $generator->generate($question, $chunks);
            } catch (Throwable $exception) {
                Log::error('Sandbox answer request failed', ['exception' => $exception]);

                return back()->withInput()->withErrors(['question' => 'The answer service is temporarily unavailable. Please try again.']);
            }
        }

        $validIds = $chunks->pluck('id')->all();
        $citationIds = array_values(array_unique(array_filter(
            $result['citation_ids'],
            fn (int $id) => in_array($id, $validIds, true)
        )));
        $answerText = $citationIds === []
            ? 'I could not find enough evidence in your Sandbox sources to answer that question.'
            : $result['answer'];

        $answer = DB::transaction(function () use ($user, $question, $answerText, $citationIds, $limit): ?Answer {
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();
            $usage = DailyAnswerUsage::where('user_id', $user->id)->whereDate('usage_date', today())->lockForUpdate()->first();
            if (($usage->answer_count ?? 0) >= $limit) {
                return null;
            }
            $answer = Answer::create(['user_id' => $user->id, 'question' => $question, 'answer' => $answerText]);
            foreach ($citationIds as $id) {
                $answer->citations()->create(['document_chunk_id' => $id]);
            }
            if ($usage) {
                $usage->increment('answer_count');
            } else {
                DailyAnswerUsage::create(['user_id' => $user->id, 'usage_date' => today(), 'answer_count' => 1]);
            }

            return $answer;
        });

        if (! $answer) {
            return back()->withInput()->withErrors(['question' => "You have reached today's Sandbox limit of $limit saved answers."]);
        }

        return redirect()->to(route('sandbox.index').'#answer-'.$answer->id)->with('toast', [
            'type' => $citationIds === [] ? 'info' : 'success',
            'message' => $citationIds === [] ? 'No supporting passage found. Try a more specific question.' : 'Your Sandbox answer is ready with source passages.',
        ]);
    }

    public function addSample(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        if (KnowledgeDocument::where('user_id', $userId)->where('name', self::SAMPLE_NAME)->exists()) {
            return redirect()->route('sandbox.index')->with('toast', [
                'type' => 'info',
                'message' => 'The starter source is already in your library.',
            ]);
        }

        $limit = config('plans.'.$request->user()->planAccess->plan.'.document_limit');

        if (KnowledgeDocument::where('user_id', $userId)->count() >= $limit) {
            return redirect()->route('sandbox.index')->with('toast', [
                'type' => 'error',
                'message' => "Your library has reached its $limit document limit.",
            ]);
        }

        $path = 'documents/'.$userId.'/'.self::SAMPLE_NAME;
        Storage::disk('local')->put($path, self::SAMPLE_TEXT);

        $document = KnowledgeDocument::create([
            'user_id' => $userId,
            'name' => self::SAMPLE_NAME,
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => strlen(self::SAMPLE_TEXT),
            'status' => 'processing',
            'processing_stage' => 'queued',
            'stage_updated_at' => now(),
        ]);

        ProcessDocument::dispatchSync($document->id);

        return redirect()->route('sandbox.index')->with('toast', [
            'type' => 'success',
            'message' => 'The starter source is ready in your library.',
        ]);
    }
}

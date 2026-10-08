<?php

namespace App\Http\Controllers;

use App\Contracts\AnswerGenerator;
use App\Models\Answer;
use App\Models\DailyAnswerUsage;
use App\Models\KnowledgeDocument;
use App\Services\DocumentSearch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AnswerController extends Controller
{
    public function index(Request $request): View
    {
        $rawSearch = $request->query('q', '');
        $search = is_string($rawSearch) ? mb_substr(trim($rawSearch), 0, 100) : '';
        $status = $request->query('status', 'all');
        $status = in_array($status, ['all', 'cited', 'insufficient', 'favorite'], true) ? $status : 'all';

        return view('answers.index', [
            'answers' => Answer::where('user_id', $request->user()->id)
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('question', 'like', '%'.addcslashes($search, '%_\\').'%')
                    ->orWhere('answer', 'like', '%'.addcslashes($search, '%_\\').'%')))
                ->when($status === 'cited', fn ($query) => $query->whereHas('citations'))
                ->when($status === 'insufficient', fn ($query) => $query->whereDoesntHave('citations'))
                ->when($status === 'favorite', fn ($query) => $query->where('is_favorite', true))
                ->latest()->paginate(12)->withQueryString(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(Request $request): View
    {
        $access = $request->user()->planAccess;
        $limit = (int) config('plans.'.$access->plan.'.daily_question_limit');
        $used = (int) (DailyAnswerUsage::where('user_id', $request->user()->id)->whereDate('usage_date', today())->value('answer_count') ?? 0);

        return view('answers.create', [
            'readyCount' => KnowledgeDocument::where('user_id', $request->user()->id)->where('status', 'ready')->count(),
            'processingCount' => KnowledgeDocument::where('user_id', $request->user()->id)->where('status', 'processing')->count(),
            'remaining' => max(0, $limit - $used),
            'limit' => $limit,
            'serviceConfigured' => filled(config('ai.providers.groq.key')),
        ]);
    }

    public function store(Request $request, DocumentSearch $search, AnswerGenerator $generator): RedirectResponse
    {
        $request->merge(['question' => trim((string) $request->input('question'))]);

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $user = $request->user();

        $limit = (int) config('plans.'.$user->planAccess->plan.'.daily_question_limit');

        if ((int) (DailyAnswerUsage::where('user_id', $user->id)->whereDate('usage_date', today())->value('answer_count') ?? 0) >= $limit) {
            return back()->withInput()->withErrors(['question' => "You have reached today’s $limit saved-answer limit. Please return tomorrow."]);
        }

        if (! KnowledgeDocument::where('user_id', $user->id)->where('status', 'ready')->exists()) {
            return redirect()->route('documents.index')->with('toast', [
                'type' => 'info',
                'message' => 'Add a ready document before asking a question.',
            ]);
        }

        $question = trim($validated['question']);
        $chunks = $search->search($user->id, $question);

        if ($chunks->isEmpty()) {
            $result = ['answer' => 'I could not find enough evidence in your documents to answer that question.', 'citation_ids' => []];
        } else {
            if (blank(config('ai.providers.groq.key'))) {
                return back()->withInput()->withErrors(['question' => 'Answers are unavailable until a Groq API key is configured.']);
            }

            try {
                $result = $generator->generate($question, $chunks);
            } catch (Throwable $exception) {
                Log::error('Groq answer request failed', ['exception' => $exception]);

                return back()->withInput()->withErrors(['question' => 'The answer service is temporarily unavailable. Please try again.']);
            }
        }

        $validIds = $chunks->pluck('id')->all();
        $citationIds = array_values(array_unique(array_filter(
            $result['citation_ids'],
            fn (int $id) => in_array($id, $validIds, true)
        )));
        $answerText = $citationIds === []
            ? 'I could not find enough evidence in your documents to answer that question.'
            : $result['answer'];

        $answer = DB::transaction(function () use ($user, $question, $answerText, $citationIds, $limit): ?Answer {
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();
            $usage = DailyAnswerUsage::where('user_id', $user->id)->whereDate('usage_date', today())->lockForUpdate()->first();
            if (($usage->answer_count ?? 0) >= $limit) {
                return null;
            }
            $answer = Answer::create([
                'user_id' => $user->id,
                'question' => $question,
                'answer' => $answerText,
            ]);

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
            return back()->withInput()->withErrors(['question' => "You have reached today’s $limit saved answer limit. Please return tomorrow."]);
        }

        return redirect()->route('answers.show', $answer)->with('toast', [
            'type' => $citationIds === [] ? 'info' : 'success',
            'message' => $citationIds === [] ? 'No supporting passage found. Try a more specific question.' : 'Answer ready. Review the supporting passages below.',
        ]);
    }

    public function show(Request $request, Answer $answer): View
    {
        abort_unless($answer->user_id === $request->user()->id, 404);

        $answer->forceFill(['viewed_at' => now()])->save();

        return view('answers.show', [
            'answer' => $answer->load('citations.chunk.document'),
        ]);
    }

    public function favorite(Request $request, Answer $answer): RedirectResponse
    {
        abort_unless($answer->user_id === $request->user()->id, 404);
        $validated = $request->validate(['favorite' => ['required', 'boolean']]);
        $answer->update(['is_favorite' => $validated['favorite']]);

        return back()->with('toast', ['type' => 'success', 'message' => $answer->is_favorite ? 'Answer saved to favorites.' : 'Answer removed from favorites.']);
    }

    public function note(Request $request, Answer $answer): RedirectResponse
    {
        abort_unless($answer->user_id === $request->user()->id, 404);
        $validated = $request->validate(['private_note' => ['nullable', 'string', 'max:5000']]);
        $answer->update(['private_note' => $validated['private_note']]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Your private note has been saved.']);
    }

    public function export(Request $request, Answer $answer): Response
    {
        abort_unless($answer->user_id === $request->user()->id, 404);
        $includeNote = $request->boolean('include_note');
        $answer->load('citations.chunk.document');
        $pdf = Pdf::loadView('answers.export', compact('answer', 'includeNote'))->setPaper('a4');

        return $pdf->download('logicstrand-answer-'.$answer->id.'.pdf');
    }
}

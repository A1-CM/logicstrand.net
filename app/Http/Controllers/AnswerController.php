<?php

namespace App\Http\Controllers;

use App\Contracts\AnswerGenerator;
use App\Models\Answer;
use App\Services\DocumentSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AnswerController extends Controller
{
    public function index(Request $request): View
    {
        return view('answers.index', [
            'answers' => Answer::where('user_id', $request->user()->id)->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('answers.create');
    }

    public function store(Request $request, DocumentSearch $search, AnswerGenerator $generator): RedirectResponse
    {
        $request->merge(['question' => trim((string) $request->input('question'))]);

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $user = $request->user();

        $limit = config('plans.'.$user->planAccess->plan.'.daily_question_limit');

        if (Answer::where('user_id', $user->id)->whereDate('created_at', today())->count() >= $limit) {
            return back()->withInput()->withErrors(['question' => "You have reached today’s $limit question limit. Please return tomorrow."]);
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

        $answer = DB::transaction(function () use ($user, $question, $answerText, $citationIds): Answer {
            $answer = Answer::create([
                'user_id' => $user->id,
                'question' => $question,
                'answer' => $answerText,
            ]);

            foreach ($citationIds as $id) {
                $answer->citations()->create(['document_chunk_id' => $id]);
            }

            return $answer;
        });

        return redirect()->route('answers.show', $answer)->with('toast', [
            'type' => $citationIds === [] ? 'info' : 'success',
            'message' => $citationIds === [] ? 'No supporting passage found. Try a more specific question.' : 'Answer ready. Review the supporting passages below.',
        ]);
    }

    public function show(Request $request, Answer $answer): View
    {
        abort_unless($answer->user_id === $request->user()->id, 404);

        return view('answers.show', [
            'answer' => $answer->load('citations.chunk.document'),
        ]);
    }
}

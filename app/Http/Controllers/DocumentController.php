<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocument;
use App\Models\Answer;
use App\Models\KnowledgeDocument;
use App\Models\PlanAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $access = PlanAccess::where('user_id', $request->user()->id)->first();

        $status = $request->query('status', 'all');
        $status = in_array($status, ['all', 'ready', 'processing', 'failed'], true) ? $status : 'all';
        $query = KnowledgeDocument::where('user_id', $request->user()->id);
        $documents = (clone $query)->when($status !== 'all', fn ($query) => $query->where('status', $status))->latest()->paginate(12)->withQueryString();
        $progressDocuments = [];
        foreach ($documents->items() as $item) {
            if ($item->status !== 'processing') {
                continue;
            }
            $progressDocuments[$item->id] = [
                'statusUrl' => URL::temporarySignedRoute('documents.progress', now()->addMinutes(30), ['document' => $item]),
                'processUrl' => route('documents.process', $item),
                'autoStart' => config('queue.default') === 'sync',
                'stage' => $item->processing_stage ?: 'queued',
                'startedAt' => $item->processing_started_at?->timestamp,
            ];
        }

        return view('documents.index', [
            'documents' => $documents,
            'status' => $status,
            'counts' => [
                'all' => (clone $query)->count(),
                'ready' => (clone $query)->where('status', 'ready')->count(),
                'processing' => (clone $query)->where('status', 'processing')->count(),
                'failed' => (clone $query)->where('status', 'failed')->count(),
            ],
            'limit' => config('plans.'.($access ? $access->plan : 'sandbox').'.document_limit'),
            'progressDocuments' => $progressDocuments,
        ]);
    }

    public function retry(Request $request, KnowledgeDocument $document): RedirectResponse
    {
        abort_unless($document->user_id === $request->user()->id, 404);
        if (! Storage::disk('local')->exists($document->path)) {
            return back()->with('toast', ['type' => 'error', 'message' => 'The original file is missing. Please upload it again.']);
        }
        $claimed = KnowledgeDocument::whereKey($document->id)->where('user_id', $request->user()->id)->where('status', 'failed')->update([
            'status' => 'processing', 'processing_stage' => 'queued', 'processing_started_at' => null,
            'stage_updated_at' => now(), 'error' => null,
        ]);
        if ($claimed !== 1) {
            return back()->with('toast', ['type' => 'info', 'message' => 'This document is already being prepared or is ready.']);
        }
        try {
            ProcessDocument::dispatchSync($document->id);
        } catch (Throwable $exception) {
            report($exception);
            KnowledgeDocument::whereKey($document->id)->where('status', 'processing')->update([
                'status' => 'failed', 'processing_stage' => 'failed', 'stage_updated_at' => now(),
                'error' => 'We could not start extraction. Please try again.',
            ]);

            return back()->with('toast', ['type' => 'error', 'message' => 'We could not start extraction. Please try again.']);
        }

        return redirect()->route('documents.index')->with('toast', ['type' => 'success', 'message' => 'We are preparing your document again.']);
    }

    public function progress(Request $request, KnowledgeDocument $document): JsonResponse
    {
        abort_unless($document->user_id === $request->user()->id, 404);

        return response()->json([
            'status' => $document->status,
            'stage' => $document->processing_stage,
            'started_at' => $document->processing_started_at?->toIso8601String(),
            'elapsed_seconds' => ($document->processing_started_at ?? $document->stage_updated_at)
                ? max(0, (int) ($document->processing_started_at ?? $document->stage_updated_at)->diffInSeconds(now()))
                : 0,
            'error' => $document->error,
        ])->header('Cache-Control', 'no-store');
    }

    public function process(Request $request, KnowledgeDocument $document): RedirectResponse|JsonResponse
    {
        abort_unless($document->user_id === $request->user()->id, 404);

        if (! Storage::disk('local')->exists($document->path)) {
            KnowledgeDocument::whereKey($document->id)->where('user_id', $request->user()->id)->update([
                'status' => 'failed', 'processing_stage' => 'failed', 'stage_updated_at' => now(),
                'error' => 'The original file is missing. Please upload it again.',
            ]);

            return $this->processingResponse($request, false, 'The original file is missing. Please upload it again.');
        }

        $staleBefore = now()->subMinutes(5);
        $claimed = KnowledgeDocument::whereKey($document->id)
            ->where('user_id', $request->user()->id)
            ->where(function ($query) use ($staleBefore): void {
                $query->where(function ($query): void {
                    $query->where('status', 'processing')->where('processing_stage', 'queued');
                })->orWhere(function ($query) use ($staleBefore): void {
                    $query->where('status', 'processing')->where('processing_started_at', '<', $staleBefore);
                });
            })
            ->update([
                'processing_stage' => 'extracting',
                'processing_started_at' => now(),
                'stage_updated_at' => now(),
                'error' => null,
            ]);

        if ($claimed !== 1) {
            return $this->processingResponse($request, false, 'This document is already being prepared or is ready.');
        }

        try {
            if (config('queue.default') === 'sync') {
                ProcessDocument::dispatchSync($document->id);
            } else {
                ProcessDocument::dispatch($document->id);
            }
        } catch (Throwable $exception) {
            KnowledgeDocument::whereKey($document->id)->where('status', 'processing')->update([
                'status' => 'failed', 'processing_stage' => 'failed', 'stage_updated_at' => now(),
                'error' => 'We could not start extraction. Please try again.',
            ]);
            report($exception);

            return $this->processingResponse($request, false, 'We could not start extraction. Please try again.');
        }

        return $this->processingResponse($request, true, 'We are preparing your document.');
    }

    private function processingResponse(Request $request, bool $success, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => $success, 'message' => $message], $success ? 200 : 409);
        }

        return redirect()->route('documents.index')->with('toast', [
            'type' => $success ? 'success' : 'info', 'message' => $message,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'document' => ['required', File::types(['txt', 'pdf'])->max('10mb')],
        ]);

        $limit = config('plans.'.$request->user()->planAccess->plan.'.document_limit');

        if (KnowledgeDocument::where('user_id', $request->user()->id)->count() >= $limit) {
            if ($request->expectsJson()) {
                return response()->json(['message' => "Your workspace has reached its $limit document limit."], 422);
            }

            return back()->withErrors(['document' => "Your workspace has reached its $limit document limit."]);
        }

        $file = $validated['document'];
        $path = $file->store('documents/'.$request->user()->id, 'local');

        $document = KnowledgeDocument::create([
            'user_id' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'processing',
            'processing_stage' => 'queued',
            'stage_updated_at' => now(),
        ]);

        if ($request->expectsJson() && config('queue.default') === 'sync') {
            return response()->json(['success' => true, 'redirect' => route('documents.index', ['watch' => $document->id])], 201);
        }

        ProcessDocument::dispatch($document->id);

        return redirect()->route('documents.index')->with('toast', ['type' => 'success', 'message' => 'Your document is being prepared.']);
    }

    public function download(Request $request, KnowledgeDocument $document): StreamedResponse
    {
        abort_unless($document->user_id === $request->user()->id, 404);

        return Storage::disk('local')->download($document->path, $document->name);
    }

    public function destroy(Request $request, KnowledgeDocument $document): RedirectResponse
    {
        abort_unless($document->user_id === $request->user()->id, 404);

        DB::transaction(function () use ($document): void {
            $chunkIds = $document->chunks()->pluck('id');
            $answerIds = DB::table('answer_citations')
                ->whereIn('document_chunk_id', $chunkIds)
                ->pluck('answer_id');

            Answer::whereIn('id', $answerIds)->delete();

            $document->delete();
        });

        Storage::disk('local')->delete($document->path);

        return redirect()->route('documents.index')->with('toast', ['type' => 'success', 'message' => 'Document and related answers removed.']);
    }
}

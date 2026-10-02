<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocument;
use App\Models\Answer;
use App\Models\KnowledgeDocument;
use App\Models\PlanAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $access = PlanAccess::where('user_id', $request->user()->id)->first();

        return view('documents.index', [
            'documents' => KnowledgeDocument::where('user_id', $request->user()->id)->latest()->get(),
            'limit' => config('plans.'.($access ? $access->plan : 'sandbox').'.document_limit'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document' => ['required', File::types(['txt', 'pdf'])->max('10mb')],
        ]);

        $limit = config('plans.'.$request->user()->planAccess->plan.'.document_limit');

        if (KnowledgeDocument::where('user_id', $request->user()->id)->count() >= $limit) {
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
        ]);

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

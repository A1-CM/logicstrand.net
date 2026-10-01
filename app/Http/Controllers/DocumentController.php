<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocument;
use App\Models\Answer;
use App\Models\KnowledgeDocument;
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
        return view('documents.index', [
            'documents' => KnowledgeDocument::where('user_id', $request->user()->id)->latest()->get(),
            'limit' => config('logicstrand.document_limit'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document' => ['required', File::types(['txt', 'pdf'])->max('10mb')],
        ]);

        if (KnowledgeDocument::where('user_id', $request->user()->id)->count() >= config('logicstrand.document_limit')) {
            return back()->withErrors(['document' => 'Your workspace has reached the 20 document limit.']);
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

        return redirect()->route('documents.index')->with('status', 'Your document is being prepared.');
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

        return redirect()->route('documents.index')->with('status', 'Document and related answers removed.');
    }
}

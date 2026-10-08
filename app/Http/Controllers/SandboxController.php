<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocument;
use App\Models\Answer;
use App\Models\KnowledgeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

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

        return view('sandbox', [
            'access' => $request->user()->planAccess,
            'documentCount' => KnowledgeDocument::where('user_id', $userId)->count(),
            'readyCount' => KnowledgeDocument::where('user_id', $userId)->where('status', 'ready')->count(),
            'answerCount' => Answer::where('user_id', $userId)->count(),
            'hasSample' => KnowledgeDocument::where('user_id', $userId)->where('name', self::SAMPLE_NAME)->exists(),
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

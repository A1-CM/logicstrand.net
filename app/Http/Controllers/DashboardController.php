<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\KnowledgeDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $userId = $request->user()->id;

        return view('dashboard', [
            'readyDocuments' => KnowledgeDocument::where('user_id', $userId)->where('status', 'ready')->count(),
            'processingDocuments' => KnowledgeDocument::where('user_id', $userId)->where('status', 'processing')->count(),
            'answerCount' => Answer::where('user_id', $userId)->count(),
            'recentAnswers' => Answer::where('user_id', $userId)->latest()->limit(4)->get(),
            'recentDocuments' => KnowledgeDocument::where('user_id', $userId)->latest()->limit(4)->get(),
        ]);
    }
}

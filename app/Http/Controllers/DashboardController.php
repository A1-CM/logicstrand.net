<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\DailyAnswerUsage;
use App\Models\KnowledgeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $pendingPlan = $request->session()->get('checkout.plan');

        if (is_string($pendingPlan) && config("plans.$pendingPlan")) {
            return redirect()->route('checkout.show', $pendingPlan);
        }

        $userId = $request->user()->id;

        $access = $request->user()->planAccess;
        $plan = $access ? $access->plan : 'sandbox';
        $dailyLimit = (int) config("plans.$plan.daily_question_limit");
        $documentLimit = (int) config("plans.$plan.document_limit");

        $starter = KnowledgeDocument::where('user_id', $userId)->where('name', 'starter-renewal-policy.txt')->where('status', 'ready')->first();
        $sampleAnswer = Answer::where('user_id', $userId)->where('question', 'What approvals are required before renewal?')->latest()->first();
        $onboardingSteps = [
            'source' => (bool) $starter,
            'question' => (bool) $sampleAnswer,
            'review' => (bool) $sampleAnswer?->viewed_at,
        ];
        $showOnboarding = $request->user()->onboarding_dismissed_at === null && ! collect($onboardingSteps)->every(fn ($done) => $done);

        return view('dashboard', [
            'access' => $access,
            'documentLimit' => $documentLimit,
            'dailyLimit' => $dailyLimit,
            'documentCount' => KnowledgeDocument::where('user_id', $userId)->count(),
            'failedDocuments' => KnowledgeDocument::where('user_id', $userId)->where('status', 'failed')->count(),
            'usedToday' => (int) (DailyAnswerUsage::where('user_id', $userId)->whereDate('usage_date', today())->value('answer_count') ?? 0),
            'readyDocuments' => KnowledgeDocument::where('user_id', $userId)->where('status', 'ready')->count(),
            'processingDocuments' => KnowledgeDocument::where('user_id', $userId)->where('status', 'processing')->count(),
            'answerCount' => Answer::where('user_id', $userId)->count(),
            'recentAnswers' => Answer::where('user_id', $userId)->latest()->limit(4)->get(),
            'recentDocuments' => KnowledgeDocument::where('user_id', $userId)->latest()->limit(4)->get(),
            'onboardingSteps' => $onboardingSteps,
            'showOnboarding' => $showOnboarding,
            'starterDocument' => $starter,
            'sampleAnswer' => $sampleAnswer,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function dismiss(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['onboarding_dismissed_at' => now()])->save();

        return back()->with('toast', ['type' => 'success', 'message' => 'Setup checklist dismissed. You can continue using your workspace.']);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspacePlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $access = $request->user()?->planAccess;

        if (! $access || ! $access->isActive()) {
            $pendingPlan = $request->session()->get('checkout.plan');

            if ($request->routeIs('dashboard') && is_string($pendingPlan) && config("plans.$pendingPlan")) {
                return redirect()->route('checkout.show', $pendingPlan);
            }

            return redirect()->route('pricing')->with('toast', [
                'type' => 'info',
                'message' => 'Choose an active plan to access your LogicStrand workspace.',
            ]);
        }

        if ($access->plan === 'sandbox' && ! $request->routeIs('dashboard', 'sandbox.index', 'sandbox.source')) {
            return response()->view('workspace.plan-required');
        }

        return $next($request);
    }
}

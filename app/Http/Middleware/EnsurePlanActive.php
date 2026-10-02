<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->planAccess?->isActive()) {
            return redirect()->route('pricing')->with('toast', [
                'type' => 'info',
                'message' => 'Choose a plan to continue working with your sources.',
            ]);
        }

        return $next($request);
    }
}

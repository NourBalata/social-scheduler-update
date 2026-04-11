<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->hasPlan()) {
            return redirect()->route('plans.index')
                ->with('error', 'You need to subscribe first');
        }

        return $next($request);
    }
}
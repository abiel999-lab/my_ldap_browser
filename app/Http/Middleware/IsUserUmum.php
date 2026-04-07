<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IsUserUmum
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):((Response|RedirectResponse))  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->user()->isInternal()) {
            return $next($request);
        }

        return back()->with('error', 'Forbidden.');
    }
}

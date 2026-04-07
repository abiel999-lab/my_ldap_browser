<?php

namespace App\Http\Middleware;

use App\Helpers\Auth\UserHelper;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):((Response|RedirectResponse))  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$role)
    {
        $userHasRole = UserHelper::userHasRole($role);
        if (! $userHasRole) {
            return back()->with('error', 'Peran anda tidak diperbolehkan mengakses halaman ini.');
        }

        return $next($request);
    }
}

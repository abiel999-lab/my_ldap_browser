<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictLdapAdminApi
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local')) {
            return $next($request);
        }

        $allowedIps = [
            '127.0.0.1',
            '::1',
        ];

        if (! in_array((string) $request->ip(), $allowedIps, true)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
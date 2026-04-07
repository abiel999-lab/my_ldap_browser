<?php

namespace App\Http\Middleware;

use App\Services\Ldap\LdapConnectionHealthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePetraNetworkForPanel
{
    public function __construct(
        protected LdapConnectionHealthService $ldapConnectionHealthService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        if ($request->routeIs('petra.network.required')) {
            return $next($request);
        }

        $isLdapReachable = $this->ldapConnectionHealthService->check();

        if ($isLdapReachable) {
            session([
                'petra_network_checked_at' => now()->toDateTimeString(),
                'petra_network_ok' => true,
            ]);

            return $next($request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('petra.network.required');
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\Oidc\OidcSessionAuthenticator;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOidcAdminRoleWeb
{
    public function __construct(
        protected OidcSessionAuthenticator $sessionAuthenticator
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('petra_auth.enabled', true)) {
            return $next($request);
        }

        if (
            $request->routeIs('login') ||
            $request->routeIs('auth.callback') ||
            $request->routeIs('forbidden') ||
            $request->routeIs('logout') ||
            $request->routeIs('filament.app.auth.logout') ||
            $request->routeIs('petra.network.required')
        ) {
            return $next($request);
        }

        logger()->info('ENSURE OIDC ADMIN ROLE WEB', [
            'route' => optional($request->route())->getName(),
            'url' => $request->fullUrl(),
            'has_session_user' => (bool) $this->sessionAuthenticator->user(),
            'auth_check' => Auth::check(),
        ]);

        $sessionUser = $this->sessionAuthenticator->user();

        if (! $sessionUser) {
            $request->session()->put('petra_auth.intended_url', $request->fullUrl());

            return redirect()->route('login');
        }

        if (! ($sessionUser['authorized'] ?? false)) {
            Auth::logout();
            $this->sessionAuthenticator->logout();

            return redirect()
                ->route('forbidden')
                ->with('error', 'Akses ditolak. Hanya user dengan group app-web/admin-role-web yang boleh masuk.');
        }

        return $next($request);
    }
}

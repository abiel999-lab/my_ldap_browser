<?php

namespace App\Http\Middleware;

use App\Services\Sso\SamlSessionAuthenticator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSamlAdminRoleWeb
{
    public function __construct(
        protected SamlSessionAuthenticator $authenticator
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('petra_sso.enabled', false)) {
            return $next($request);
        }

        $isSamlRoute = str_starts_with($request->path(), 'saml2/');
        $isForbiddenRoute = $request->routeIs('sso.forbidden');
        $isSsoRedirectRoute = $request->routeIs('sso.redirect');
        $isLogoutRoute = $request->routeIs('logout') || $request->routeIs('filament.app.auth.logout');

        if ($isSamlRoute || $isForbiddenRoute || $isSsoRedirectRoute || $isLogoutRoute) {
            return $next($request);
        }

        $user = $this->authenticator->user();

        if (! $user) {
            return redirect()->route('sso.redirect');
        }

        if (! ($user['authorized'] ?? false)) {
            $this->authenticator->logout();

            return redirect()
                ->route('sso.forbidden')
                ->with('error', 'Akses ditolak. Hanya user dengan group /app-web/admin-role-web yang boleh masuk.');
        }

        return $next($request);
    }
}

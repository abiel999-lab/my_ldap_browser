<?php

namespace App\Http\Middleware;

use App\Services\Sso\SamlSessionAuthenticator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSamlSessionExists
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

        if (! $this->authenticator->check()) {
            return redirect()->route('sso.redirect');
        }

        return $next($request);
    }
}

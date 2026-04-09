<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Oidc\OidcSessionAuthenticator;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePanelAccess
{
    public function __construct(
        private readonly OidcSessionAuthenticator $sessionAuthenticator
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        logger()->info('AUTH PANEL ACCESS CHECK', [
            'auth_check' => Auth::check(),
            'session_has_oidc_user' => (bool) $this->sessionAuthenticator->user(),
            'url' => $request->fullUrl(),
        ]);
        if (Auth::check()) {
            return $next($request);
        }

        $sessionUser = $this->sessionAuthenticator->user();

        if (! $sessionUser) {
            $request->session()->put('petra_auth.intended_url', $request->fullUrl());

            return new RedirectResponse(route('login'));
        }

        $email = (string) ($sessionUser['email'] ?? '');
        $name = (string) ($sessionUser['name'] ?? 'Petra User');
        $sub = (string) ($sessionUser['sub'] ?? $email);

        $user = User::query()->firstOrNew([
            'email' => $email !== '' ? $email : $sub,
        ]);

        $user->name = $name;
        $user->email = $email !== '' ? $email : ($user->email ?: $sub);

        if (! $user->exists) {
            $user->password = bcrypt(\Illuminate\Support\Str::random(32));
        }

        $user->save();

        Auth::login($user, true);

        return $next($request);
    }
}

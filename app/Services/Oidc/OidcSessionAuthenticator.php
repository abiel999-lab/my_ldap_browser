<?php

namespace App\Services\Oidc;

class OidcSessionAuthenticator
{
    public const SESSION_KEY = 'petra_oidc_user';

    public function login(array $payload): void
    {
        session()->put(self::SESSION_KEY, $payload);
        session()->save();
    }

    public function user(): ?array
    {
        return session()->get(self::SESSION_KEY);
    }

    public function check(): bool
    {
        return session()->has(self::SESSION_KEY);
    }

    public function logout(): void
    {
        session()->forget(self::SESSION_KEY);
        session()->forget('petra_auth.intended_url');
        session()->forget('petra_oidc_state');
        session()->forget('petra_oidc_nonce');
    }
}

<?php

namespace App\Services\Sso;

use Illuminate\Support\Facades\Session;

class SamlSessionAuthenticator
{
    public const SESSION_KEY = 'petra_saml_user';

    public function login(array $payload): void
    {
        Session::put(self::SESSION_KEY, $payload);
        Session::regenerate();
    }

    public function user(): ?array
    {
        return Session::get(self::SESSION_KEY);
    }

    public function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public function logout(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::invalidate();
        Session::regenerateToken();
    }
}

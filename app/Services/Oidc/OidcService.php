<?php

namespace App\Services\Oidc;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class OidcService
{
    public function getAuthorizationUrl(string $state, string $nonce): string
    {
        $query = http_build_query([
            'client_id' => config('services.keycloak.client_id'),
            'redirect_uri' => config('services.keycloak.redirect_uri'),
            'response_type' => 'code',
            'scope' => config('services.keycloak.scope', 'openid profile email'),
            'state' => $state,
            'nonce' => $nonce,
        ]);

        return $this->realmBaseUrl() . '/protocol/openid-connect/auth?' . $query;
    }

    public function exchangeCodeForTokens(string $code): array
    {
        $response = $this->http()
            ->asForm()
            ->timeout(20)
            ->post($this->realmBaseUrl() . '/protocol/openid-connect/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.keycloak.client_id'),
                'client_secret' => config('services.keycloak.client_secret'),
                'redirect_uri' => config('services.keycloak.redirect_uri'),
                'code' => $code,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal menukar authorization code ke token. Response: ' . $response->body());
        }

        return $response->json();
    }

    public function fetchUserInfo(string $accessToken): array
    {
        $response = $this->http()
            ->withToken($accessToken)
            ->timeout(20)
            ->get($this->realmBaseUrl() . '/protocol/openid-connect/userinfo');

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal mengambil userinfo dari Keycloak. Response: ' . $response->body());
        }

        return $response->json();
    }

    public function buildLogoutUrl(?string $idTokenHint = null): string
    {
        $params = [
            'post_logout_redirect_uri' => config('services.keycloak.post_logout_redirect_uri'),
            'client_id' => config('services.keycloak.client_id'),
        ];

        if ($idTokenHint) {
            $params['id_token_hint'] = $idTokenHint;
        }

        return $this->realmBaseUrl() . '/protocol/openid-connect/logout?' . http_build_query($params);
    }

    public function buildSessionPayload(array $tokens, array $userInfo): array
    {
        $groups = $this->normalizeGroups($userInfo[config('petra_auth.group_claim', 'groups')] ?? []);
        $allowedGroup = $this->normalizeGroup(config('petra_auth.allowed_group', 'app-web/admin-role-web'));

        $email = (string) (
            $userInfo['email']
            ?? $userInfo['preferred_username']
            ?? $userInfo['sub']
            ?? ''
        );

        $name = (string) (
            $userInfo['name']
            ?? $userInfo['preferred_username']
            ?? $email
            ?? 'Petra User'
        );

        logger()->info('OIDC GROUP DEBUG', [
            'raw_userinfo_groups' => $userInfo[config('petra_auth.group_claim', 'groups')] ?? null,
            'normalized_groups' => $groups,
            'allowed_group' => $allowedGroup,
        ]);

        return [
            'sub' => (string) ($userInfo['sub'] ?? $email),
            'email' => $email,
            'name' => $name,
            'preferred_username' => (string) ($userInfo['preferred_username'] ?? $email),
            'groups' => $groups,
            'authorized' => in_array($allowedGroup, $groups, true),
            'access_token' => (string) ($tokens['access_token'] ?? ''),
            'refresh_token' => (string) ($tokens['refresh_token'] ?? ''),
            'id_token' => (string) ($tokens['id_token'] ?? ''),
            'userinfo' => $userInfo,
        ];
    }

    private function http(): PendingRequest
    {
        $request = Http::acceptJson();

        $verify = filter_var(env('KEYCLOAK_TLS_VERIFY', true), FILTER_VALIDATE_BOOL);

        if (! $verify) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    private function normalizeGroups(array|string|null $groups): array
    {
        if (is_null($groups)) {
            return [];
        }

        if (is_string($groups)) {
            $groups = [$groups];
        }

        if (! is_array($groups)) {
            return [];
        }

        $normalized = [];

        array_walk_recursive($groups, function ($value) use (&$normalized) {
            if (is_string($value) && trim($value) !== '') {
                $normalized[] = $this->normalizeGroup($value);
            }
        });

        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }

    private function normalizeGroup(string $group): string
    {
        return trim(trim($group), '/');
    }

    private function realmBaseUrl(): string
    {
        return rtrim(config('services.keycloak.base_url'), '/')
            . '/realms/' . trim((string) config('services.keycloak.realm'), '/');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Oidc\OidcService;
use App\Services\Oidc\OidcSessionAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected OidcService $oidcService,
        protected OidcSessionAuthenticator $sessionAuthenticator,
    ) {
    }

    public function login(Request $request): RedirectResponse
    {
        Log::info('OIDC LOGIN START', [
            'auth_check' => Auth::check(),
            'session_id' => $request->session()->getId(),
            'has_portal_user' => $request->session()->has('portal_user'),
            'has_oidc_state' => $request->session()->has('petra_oidc_state'),
            'has_oidc_user' => $request->session()->has('petra_oidc_user'),
            'url' => $request->fullUrl(),
        ]);

        if (Auth::check() && ! $request->session()->has('petra_oidc_user')) {
            Log::warning('OIDC STALE AUTH SESSION DETECTED', [
                'user_id' => Auth::id(),
                'session_id' => $request->session()->getId(),
            ]);

            Auth::logout();

            $request->session()->forget([
                'petra_oidc_state',
                'petra_oidc_nonce',
                'petra_auth.intended_url',
                'portal_user',
                'portal_user_forbidden',
                'petra_oidc_user',
            ]);

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        if (Auth::check() && $request->session()->has('petra_oidc_user')) {
            Log::info('OIDC LOGIN SHORT-CIRCUIT AUTHED', [
                'user_id' => Auth::id(),
                'session_id' => $request->session()->getId(),
            ]);

            return redirect()->route('filament.app.pages.dashboard');
        }

        $state = Str::random(40);
        $nonce = Str::random(40);

        $request->session()->put('petra_oidc_state', $state);
        $request->session()->put('petra_oidc_nonce', $nonce);

        if (! $request->session()->has('petra_auth.intended_url')) {
            $intended = $request->query('redirect', route('filament.app.pages.dashboard'));

            if (is_string($intended) && str_starts_with($intended, 'http://')) {
                $intended = preg_replace('/^http:\/\//i', 'https://', $intended);
            }

            $request->session()->put('petra_auth.intended_url', $intended);
        }

        $request->session()->save();

        Log::info('OIDC LOGIN REDIRECTING TO KEYCLOAK', [
            'session_id' => $request->session()->getId(),
            'state' => $state,
            'nonce' => $nonce,
            'intended_url' => $request->session()->get('petra_auth.intended_url'),
            'authorization_url' => $this->oidcService->getAuthorizationUrl($state, $nonce),
        ]);

        return redirect()->away(
            $this->oidcService->getAuthorizationUrl($state, $nonce)
        );
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('petra_oidc_state', '');
        $incomingState = (string) $request->query('state', '');
        $code = (string) $request->query('code', '');

        Log::info('OIDC CALLBACK START', [
            'session_id' => $request->session()->getId(),
            'expected_state' => $expectedState,
            'incoming_state' => $incomingState,
            'has_code' => $code !== '',
            'full_url' => $request->fullUrl(),
            'all_query' => $request->query(),
        ]);

        if ($expectedState === '' || $incomingState === '' || ! hash_equals($expectedState, $incomingState)) {
            Log::warning('OIDC CALLBACK INVALID STATE', [
                'session_id' => $request->session()->getId(),
                'expected_state' => $expectedState,
                'incoming_state' => $incomingState,
            ]);

            return redirect()
                ->route('login')
                ->with('error', 'State OIDC tidak valid. Silakan login ulang.');
        }

        if ($code === '') {
            Log::warning('OIDC CALLBACK MISSING CODE', [
                'session_id' => $request->session()->getId(),
                'query' => $request->query(),
            ]);

            return redirect()
                ->route('login')
                ->with('error', 'Authorization code dari Keycloak tidak ditemukan.');
        }

        try {
            $tokens = $this->oidcService->exchangeCodeForTokens($code);

            Log::info('OIDC TOKEN EXCHANGE OK', [
                'session_id' => $request->session()->getId(),
                'has_access_token' => ! empty($tokens['access_token']),
                'has_refresh_token' => ! empty($tokens['refresh_token']),
                'has_id_token' => ! empty($tokens['id_token']),
            ]);

            $userInfo = $this->oidcService->fetchUserInfo((string) ($tokens['access_token'] ?? ''));
            $payload = $this->oidcService->buildSessionPayload($tokens, $userInfo);

            Log::info('OIDC USERINFO + PAYLOAD OK', [
                'session_id' => $request->session()->getId(),
                'email' => $payload['email'] ?? null,
                'name' => $payload['name'] ?? null,
                'authorized' => $payload['authorized'] ?? null,
                'groups' => $payload['groups'] ?? [],
            ]);

            $email = (string) ($payload['email'] ?? '');
            $name = (string) ($payload['name'] ?? 'Petra User');
            $sub = (string) ($payload['sub'] ?? $email);

            $avatarUrl = (string) (
                $userInfo['picture']
                ?? $userInfo['avatar_url']
                ?? $userInfo['photo']
                ?? ''
            );

            $user = User::query()->firstOrNew([
                'email' => $email !== '' ? $email : $sub,
            ]);

            $user->name = $name;
            $user->email = $email !== '' ? $email : ($user->email ?: $sub);
            $user->avatar_url = $avatarUrl !== '' ? $avatarUrl : $user->avatar_url;

            if (! $user->exists) {
                $user->password = bcrypt(Str::random(32));
            }

            $user->save();

            Log::info('OIDC USER SAVED', [
                'session_id' => $request->session()->getId(),
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            $request->session()->regenerate();

            $this->sessionAuthenticator->login($payload);
            Auth::login($user, true);

            $request->session()->put('petra_oidc_user', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'groups' => $payload['groups'] ?? [],
                'authorized' => (bool) ($payload['authorized'] ?? false),
            ]);

            $request->session()->save();

            Log::info('OIDC AFTER LOGIN', [
                'session_id' => $request->session()->getId(),
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id(),
                'has_portal_user' => $request->session()->has('portal_user'),
                'has_oidc_user' => $request->session()->has('petra_oidc_user'),
                'session_keys' => array_keys($request->session()->all()),
            ]);

            if (! ($payload['authorized'] ?? false)) {
                Log::warning('OIDC USER NOT AUTHORIZED', [
                    'session_id' => $request->session()->getId(),
                    'user_id' => $user->id,
                    'groups' => $payload['groups'] ?? [],
                ]);

                return redirect()
                    ->route('forbidden')
                    ->with('error', 'Akses ditolak. Hanya user dengan group app-web/admin-role-web yang boleh masuk.');
            }

            $intendedUrl = $request->session()->pull(
                'petra_auth.intended_url',
                route('filament.app.pages.dashboard')
            );

            if (is_string($intendedUrl) && str_starts_with($intendedUrl, 'http://')) {
                $intendedUrl = preg_replace('/^http:\/\//i', 'https://', $intendedUrl);
            }

            Log::info('OIDC CALLBACK SUCCESS REDIRECT', [
                'session_id' => $request->session()->getId(),
                'intended_url' => $intendedUrl,
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id(),
            ]);

            return redirect()->to($intendedUrl);
        } catch (\Throwable $e) {
            report($e);

            Log::error('OIDC CALLBACK EXCEPTION', [
                'session_id' => $request->session()->getId(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('login')
                ->with('error', 'OIDC callback error: ' . $e->getMessage());
        }
    }

    public function forbidden(): View
    {
        return view('filament.auth.forbidden');
    }

    public function logout(Request $request): RedirectResponse
    {
        $currentUser = $this->sessionAuthenticator->user();
        $idTokenHint = $currentUser['id_token'] ?? null;

        Log::info('OIDC LOGOUT START', [
            'session_id' => $request->session()->getId(),
            'auth_check' => Auth::check(),
            'auth_id' => Auth::id(),
        ]);

        Auth::logout();
        $this->sessionAuthenticator->logout();

        $request->session()->forget([
            'petra_oidc_state',
            'petra_oidc_nonce',
            'petra_auth.intended_url',
            'portal_user',
            'portal_user_forbidden',
            'petra_oidc_user',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away(
            $this->oidcService->buildLogoutUrl($idTokenHint)
        );
    }
}

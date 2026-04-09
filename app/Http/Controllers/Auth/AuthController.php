<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Oidc\OidcService;
use App\Services\Oidc\OidcSessionAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (Auth::check()) {
            return redirect()->route('filament.app.pages.dashboard');
        }

        $state = Str::random(40);
        $nonce = Str::random(40);

        $request->session()->put('petra_oidc_state', $state);
        $request->session()->put('petra_oidc_nonce', $nonce);

        if (! $request->session()->has('petra_auth.intended_url')) {
            $request->session()->put(
                'petra_auth.intended_url',
                $request->query('redirect', route('filament.app.pages.dashboard'))
            );
        }

        $request->session()->save();

        return redirect()->away(
            $this->oidcService->getAuthorizationUrl($state, $nonce)
        );
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('petra_oidc_state', '');
        $incomingState = (string) $request->query('state', '');
        $code = (string) $request->query('code', '');

        if ($expectedState === '' || $incomingState === '' || ! hash_equals($expectedState, $incomingState)) {
            return redirect()
                ->route('login')
                ->with('error', 'State OIDC tidak valid. Silakan login ulang.');
        }

        if ($code === '') {
            return redirect()
                ->route('login')
                ->with('error', 'Authorization code dari Keycloak tidak ditemukan.');
        }

        try {
            $tokens = $this->oidcService->exchangeCodeForTokens($code);
            $userInfo = $this->oidcService->fetchUserInfo((string) ($tokens['access_token'] ?? ''));
            $payload = $this->oidcService->buildSessionPayload($tokens, $userInfo);

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

            // Regenerate dulu sekali, lalu isi session-session baru
            $request->session()->regenerate();

            $this->sessionAuthenticator->login($payload);
            Auth::login($user, true);

            if (! ($payload['authorized'] ?? false)) {
                return redirect()
                    ->route('forbidden')
                    ->with('error', 'Akses ditolak. Hanya user dengan group app-web/admin-role-web yang boleh masuk.');
            }

            $intendedUrl = $request->session()->pull(
                'petra_auth.intended_url',
                route('filament.app.pages.dashboard')
            );

            return redirect()->to($intendedUrl);
        } catch (\Throwable $e) {
            report($e);

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

        Auth::logout();
        $this->sessionAuthenticator->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away(
            $this->oidcService->buildLogoutUrl($idTokenHint)
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LocalLoginController extends Controller
{
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if (! $this->isLocalAdminEnabled()) {
            abort(404);
        }

        if ($request->session()->get('local_admin_logged_in') === true) {
            return redirect()->intended('/');
        }

        return view('auth.local-login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (! $this->isLocalAdminEnabled()) {
            abort(404);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $expectedEmail = (string) env('LOCAL_ADMIN_EMAIL');
        $expectedPassword = (string) env('LOCAL_ADMIN_PASSWORD');
        $expectedName = (string) env('LOCAL_ADMIN_NAME', 'Local Admin');

        $emailMatched = hash_equals(
            mb_strtolower($expectedEmail),
            mb_strtolower((string) $validated['email'])
        );

        $passwordMatched = hash_equals(
            $expectedPassword,
            (string) $validated['password']
        );

        if (! $emailMatched || ! $passwordMatched) {
            return back()
                ->withErrors([
                    'email' => 'Email atau password salah.',
                ])
                ->withInput([
                    'email' => $validated['email'],
                ]);
        }

        $request->session()->regenerate();

        $request->session()->put('local_admin_logged_in', true);
        $request->session()->put('local_admin_user', [
            'id' => 'local-admin',
            'name' => $expectedName,
            'email' => $expectedEmail,
            'is_local_admin' => true,
        ]);

        $user = new User();
        $user->id = 'local-admin';
        $user->name = $expectedName;
        $user->email = $expectedEmail;
        $user->is_local_admin = true;

        Auth::setUser($user);

        return redirect()->intended('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->forget('local_admin_logged_in');
        $request->session()->forget('local_admin_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('local.login');
    }

    private function isLocalAdminEnabled(): bool
    {
        return app()->environment('local')
            && filter_var(env('LOCAL_ADMIN_ENABLED', false), FILTER_VALIDATE_BOOL);
    }
}
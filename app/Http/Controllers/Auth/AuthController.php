<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Auth\AuthHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use App\Services\GateService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    public const AUTH_LIFETIME = 60;

    public function callback(Request $request)
    {
        try {
            $response = (new AuthService)->auth($request->query(config('sso.cookie')), config('app.id'));
            $session = $response['data'];
            if (Auth::id() != $session['user']['id']) {
                if (session()->has('login_as')) {
                    $login_as = session()->get('login_as'); // ----ambil data login as
                    $cookies = session()->get(config('sso.cookie')); // ----session sebelumnya
                    // ------hapus session yang ada
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    // ------simpan user login as ke session
                    $request->session()->put('login_as', $login_as);
                    $request->session()->put(config('sso.cookie'), $cookies);
                } else {
                    // ------hapus session yang ada
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                // ------sesuaikan dengan user di lokal
                AuthHelper::setUser($session['user']);
            }
            $request->session()->put(config('sso.cookie'), $request->query(config('sso.cookie')));
            $request->session()->regenerate();

            return redirect($request->query('redirect_url'));
        } catch (Exception $e) {
            report($e);
        }

        return $this->logout($request);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away(config('url.service.auth') . '/logout');
    }

    public function changerole(Request $request)
    {
        $kode = Crypt::decryptString($request->role);
        AuthHelper::setCurrentRole($kode);

        return redirect()->route('filament.app.pages.dashboard');
    }

    public function index()
    {
        return view('index');
    }

    public function getLoginAsList()
    {
        return view('auth.login_as');
    }

    public function setLoginAs($id)
    {
        $id = Crypt::decryptString($id);

        // -----------ambil data user dari gate seperti di auth callback
        $response = (new GateService)->getUser($id);
        if (! $response) {
            return redirect()->back()->with('error', 'User tidak ditemukan');
        }
        // -----------simpan user sekarang ke session
        // $current_user = Auth::user();
        // session()->put('login_as_current', $current_user);

        // -----------kirim data user ke session untuk dibaca oleh AuthService
        session()->put('login_as', $response->toArray());

        // -----------redirect ke callback untuk refresh session user
        return redirect()->route('auth.callback', [
            'redirect_url' => route('filament.app.pages.dashboard'),
            config('sso.cookie') => session()->get(config('sso.cookie')),
        ]);
    }

    // ------------------untuk logout login as
    public function logoutLoginAs()
    {
        // -----------hapus session user login as
        session()->forget('login_as');
        // session()->forget('login_as_current');

        // -----------redirect ke callback untuk refresh session user
        return redirect()->route('auth.callback', [
            'redirect_url' => route('filament.app.pages.dashboard'),
            config('sso.cookie') => session()->get(config('sso.cookie')),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Services\Sso\SamlSessionAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class SsoController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $idpName = config('petra_sso.idp_name');

        return redirect()->route('saml2_login', [
            'idpName' => $idpName,
        ]);
    }

    public function logout(SamlSessionAuthenticator $authenticator): RedirectResponse
    {
        $authenticator->logout();

        return redirect()->route('sso.redirect');
    }

    public function forbidden(): View
    {
        return view('filament.auth.forbidden');
    }
}

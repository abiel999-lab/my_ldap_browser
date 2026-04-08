<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SsoController;
use Illuminate\Support\Facades\Route;

Route::get('/auth_callback', [AuthController::class, 'callback'])->name('auth.callback');

Route::get('/petra-network-required', function () {
    return view('petra-network-required');
})->name('petra.network.required');

/*
|--------------------------------------------------------------------------
| SSO / Keycloak
|--------------------------------------------------------------------------
*/
Route::get('/sso/login', [SsoController::class, 'redirect'])->name('sso.redirect');
Route::get('/forbidden', [SsoController::class, 'forbidden'])->name('sso.forbidden');

Route::get('/logout', [SsoController::class, 'logout'])->name('logout')->middleware('web');
Route::post('/logout', [SsoController::class, 'logout'])->name('filament.app.auth.logout')->middleware('web');

/*
|--------------------------------------------------------------------------
| Authenticated app routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'saml.session'])->group(function () {
    Route::post('changerole', [AuthController::class, 'changerole'])->name('changerole');
    Route::get('/loginas/logout', [AuthController::class, 'logoutLoginAs'])->name('loginas.logout');

    Route::middleware('role:PIC')->group(function () {
        Route::get('/loginas', [AuthController::class, 'getLoginAsList'])->name('loginas.index');
        Route::get('/loginas/{id}', [AuthController::class, 'setLoginAs'])->name('loginas.set');
    });

    Route::get('/testmail', [App\Http\Controllers\MailController::class, 'send'])->name('test.mail');
});

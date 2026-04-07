<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LocalLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/auth_callback', [AuthController::class, 'callback'])->name('auth.callback');

Route::get('/petra-network-required', function () {
    return view('petra-network-required');
})->name('petra.network.required');

if (app()->environment('local') && filter_var(env('LOCAL_ADMIN_ENABLED', false), FILTER_VALIDATE_BOOL)) {
    Route::get('/login', [LocalLoginController::class, 'showLoginForm'])->name('local.login');
    Route::post('/login', [LocalLoginController::class, 'login'])->name('local.login.submit');

    Route::get('/logout', [LocalLoginController::class, 'logout'])->name('logout');
    Route::post('/logout', [LocalLoginController::class, 'logout'])->name('filament.app.auth.logout');
} else {
    Route::middleware('auth.sso')->group(function () {
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('web');
        Route::post('/logout', [AuthController::class, 'logout'])->name('filament.app.auth.logout')->middleware('web');
        Route::post('changerole', [AuthController::class, 'changerole'])->name('changerole');
        Route::get('/loginas/logout', [AuthController::class, 'logoutLoginAs'])->name('loginas.logout');

        Route::middleware('role:PIC')->group(function () {
            Route::get('/loginas', [AuthController::class, 'getLoginAsList'])->name('loginas.index');
            Route::get('/loginas/{id}', [AuthController::class, 'setLoginAs'])->name('loginas.set');
        });

        Route::get('/testmail', [App\Http\Controllers\MailController::class, 'send'])->name('test.mail');
    });
}

<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::middleware('web')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/auth/callback', [AuthController::class, 'callback'])->name('auth.callback');

    Route::get('/forbidden', [AuthController::class, 'forbidden'])->name('forbidden');

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/logout', [AuthController::class, 'logout'])->name('filament.app.auth.logout');

    Route::view('/petra-network-required', 'petra-network-required')
        ->name('petra.network.required');
});

Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/livewire/update', $handle)->middleware(['web']);
});

<?php

namespace App\Providers;

use App\Models\Gate\User;
use App\Models\Ref\Bahasa;
use App\Policies\Admin\BahasaPolicy;
use App\Policies\Admin\UserUnitPolicy;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en', 'id', 'zh_CN']) // also accepts a closure
                // ->visible(outsidePanels: true)
                ->flags([
                    'en' => asset('flags/gb.svg'),
                    'id' => asset('flags/id.svg'),
                    'zh_CN' => asset('flags/cn.svg'),
                ])
                ->labels([
                    'en' => 'English',
                    'id' => 'Bahasa Indonesia',
                    'zh_CN' => '简体中文',
                ]);
        });

        // ----------render hook untuk select role di user menu filament
        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            function () {
                $user = Auth::user();

                if (! $user) {
                    return null;
                }

                if (($user->is_local_admin ?? false) === true) {
                    return null;
                }

                if (! method_exists($user, 'user_role')) {
                    return null;
                }

                $roles = $user->user_role();

                if (! $roles) {
                    return null;
                }

                if (method_exists($roles, 'count') && $roles->count() <= 1) {
                    return null;
                }

                return view('filament.layouts.select-role', [
                    'roles' => $roles,
                ])->render();
            }
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            function () {

                return view('layouts.footer', []);
            }
        );

        // -------daftarkan policy untuk UserUnit
        // Gate::policy(User::class, UserUnitPolicy::class);
        // Gate::policy(Bahasa::class, BahasaPolicy::class);
    }
}

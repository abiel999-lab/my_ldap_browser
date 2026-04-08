<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Ldap\LdapUserManualResource;
use App\Http\Middleware\AuthenticatePanelAccess;
use App\Http\Middleware\EnsurePetraNetworkForPanel;
use App\Http\Middleware\EnsureSamlAdminRoleWeb;
use Filament\Actions\Action;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use TomatoPHP\FilamentPWA\FilamentPWAPlugin;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('')
            ->colors([
                'primary' => Color::Blue,
                'secondary' => Color::Gray,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->unsavedChangesAlerts()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                EnsureSamlAdminRoleWeb::class,
                AuthenticatePanelAccess::class,
                EnsurePetraNetworkForPanel::class,
            ], isPersistent: true)
            ->userMenuItems([
                Action::make('user_menu_item_user_manual')
                    ->label('User Manual')
                    ->url(fn (): string => LdapUserManualResource::getUrl('index'))
                    ->icon('heroicon-o-book-open'),

                Action::make('user_menu_item_gate_pcu')
                    ->label('Gate PCU')
                    ->icon('heroicon-o-square-2-stack')
                    ->url(fn (): string => config('url.service.gate')),

                Action::make('user_menu_item_login_as_logout')
                    ->label('Log Out As')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (): string => route('loginas.logout'))
                    ->visible(fn (): bool => Auth::check() && session()->has('login_as')),
            ])
            ->renderHook(PanelsRenderHook::SIDEBAR_NAV_START, function (): string {
                return view('filament.partials.sidebar-pin-toggle', [
                    'inSidebar' => true,
                ])->render();
            })
            ->renderHook(PanelsRenderHook::HEAD_END, function (): string {
                return view('filament.partials.head-sidebar-behavior')->render();
            })
            ->favicon(asset('img/PCU.png'))
            ->brandLogo(url('https://my.petra.ac.id/img/logo.png'))
            ->darkModeBrandLogo(url('https://my.petra.ac.id/img/logo.png'))
            ->brandName('')
            ->brandLogoHeight('52px')
            ->viteTheme('resources/css/filament/app/theme.css')
            ->plugins([
                FilamentPWAPlugin::make()->allowPWASettings(false),
            ]);
    }
}

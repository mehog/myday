<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages\Auth\EditProfile;
use App\Filament\App\Pages\PricingPage;
use App\Filament\App\Pages\ReferralsPage;
use App\Http\Middleware\SetAppLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->login()
            ->brandName('NasDan')
            ->brandLogo(asset('icons/nd-logo-transparent.webp'))
            ->brandLogoHeight('2.25rem')
            ->viteTheme('resources/css/filament/app/theme.css')
            ->font('Poppins')
            ->globalSearch(false)
            ->databaseNotifications()
            ->profile(EditProfile::class, isSimple: false)
            ->topNavigation()
            ->colors([
                'primary' => Color::hex('#c9a227'),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn () => view('components.google-analytics'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('components.app.push-notifications'),
            )
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\Filament\App\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\Filament\App\Pages')
            ->pages([
                PricingPage::class,
                ReferralsPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\Filament\App\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->middleware([SetAppLocale::class], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
                EnsureEmailIsVerified::class,
            ]);
    }
}

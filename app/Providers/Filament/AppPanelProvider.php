<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages\Auth\EditProfile;
use App\Filament\App\Pages\Auth\Login;
use App\Filament\App\Pages\Auth\ResetPassword;
use App\Filament\App\Pages\PricingPage;
use App\Filament\App\Pages\ReferralsPage;
use App\Http\Middleware\EnsureEmailVerifiedOrGrace;
use App\Http\Middleware\SetAppLocale;
use App\Support\DashboardNav;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
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
            ->login(Login::class)
            ->passwordReset(resetAction: ResetPassword::class)
            ->brandName(config('app.name'))
            ->brandLogo(asset('icons/nd-logo-transparent.webp'))
            ->brandLogoHeight('2.25rem')
            ->viteTheme('resources/css/filament/app/theme.css')
            ->font('Poppins')
            ->globalSearch(false)
            ->databaseNotifications()
            ->profile(EditProfile::class, isSimple: false)
            ->topNavigation()
            ->homeUrl(fn (): string => DashboardNav::homeUrl())
            ->userMenuItems([
                Action::make('new_dashboard')
                    ->label(fn (): string => __('dashboard.new_dashboard'))
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->url(fn (): string => route('dashboard'))
                    ->sort(-1),
                Action::make('pricing')
                    ->label(fn (): string => __('pricing.nav_label'))
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->url(fn (): string => PricingPage::getUrl())
                    ->sort(0),
                Action::make('referrals')
                    ->label(fn (): string => __('referrals.nav_label'))
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->url(fn (): string => ReferralsPage::getUrl())
                    ->sort(1),
            ])
            ->colors([
                'primary' => Color::hex('#c9a227'),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn () => view('components.google-analytics'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn () => view('components.meta-pixel'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('components.app.disable-mobile-zoom'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('components.app.push-notifications'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('components.app.support-bubble'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('components.app.upgrade-required-modal'),
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
                EnsureEmailVerifiedOrGrace::class,
            ]);
    }
}

<?php

namespace App\Providers;

use App\Http\Responses\EmailVerificationResponse;
use App\Http\Responses\LoginResponse;
use App\Listeners\PreventDemoInvitationMail;
use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\WeddingEvent;
use App\Notifications\Channels\DispatchScheduledPushChannel;
use App\Observers\GuestMessageObserver;
use App\Observers\GuestObserver;
use App\Observers\WeddingEventObserver;
use App\Support\BrandTranslator;
use Filament\Auth\Http\Responses\Contracts\EmailVerificationResponse as EmailVerificationResponseContract;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\Translator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(EmailVerificationResponseContract::class, EmailVerificationResponse::class);

        $this->app->extend('translator', function (Translator $translator): BrandTranslator {
            if ($translator instanceof BrandTranslator) {
                return $translator;
            }

            $branded = new BrandTranslator($translator->getLoader(), $translator->getLocale());
            $branded->setFallback($translator->getFallback());

            return $branded;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        GuestMessage::observe(GuestMessageObserver::class);
        WeddingEvent::observe(WeddingEventObserver::class);
        Guest::observe(GuestObserver::class);

        Event::listen(MessageSending::class, PreventDemoInvitationMail::class);

        Notification::resolved(function (ChannelManager $manager): void {
            $manager->extend('dispatch-scheduled-push', fn (): DispatchScheduledPushChannel => new DispatchScheduledPushChannel);
        });
    }
}

<?php

namespace App\Providers;

use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\WeddingEvent;
use App\Notifications\Channels\DispatchScheduledPushChannel;
use App\Observers\GuestMessageObserver;
use App\Observers\GuestObserver;
use App\Observers\WeddingEventObserver;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        GuestMessage::observe(GuestMessageObserver::class);
        WeddingEvent::observe(WeddingEventObserver::class);
        Guest::observe(GuestObserver::class);

        Notification::resolved(function (ChannelManager $manager): void {
            $manager->extend('dispatch-scheduled-push', fn (): DispatchScheduledPushChannel => new DispatchScheduledPushChannel);
        });
    }
}

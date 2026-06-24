<?php

namespace App\Providers;

use App\Models\GuestMessage;
use App\Observers\GuestMessageObserver;
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
    }
}

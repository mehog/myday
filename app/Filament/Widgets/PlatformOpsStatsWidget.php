<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ReferralPayouts\ReferralPayoutResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\WeddingEvents\WeddingEventResource;
use App\Support\AdminDashboardMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOpsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending activations', (string) AdminDashboardMetrics::pendingActivationsCount())
                ->description('Weddings awaiting publish')
                ->icon('heroicon-o-clock')
                ->url(WeddingEventResource::getUrl('index')),
            Stat::make('Unverified couples', (string) AdminDashboardMetrics::unverifiedCouplesCount())
                ->description('Email not confirmed')
                ->icon('heroicon-o-envelope')
                ->url(UserResource::getUrl('index')),
            Stat::make('New signups', (string) AdminDashboardMetrics::newSignupsCount())
                ->description('Last 7 days')
                ->icon('heroicon-o-user-plus')
                ->url(UserResource::getUrl('index')),
            Stat::make('Pending payouts', (string) AdminDashboardMetrics::pendingPayoutsCount())
                ->description('Awaiting payment')
                ->icon('heroicon-o-banknotes')
                ->url(ReferralPayoutResource::getUrl('index')),
            Stat::make('Recent enquiries', (string) AdminDashboardMetrics::recentEnquiriesCount())
                ->description('Last 7 days')
                ->icon('heroicon-o-chat-bubble-left-right'),
        ];
    }
}

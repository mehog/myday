<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ReferralPayouts\ReferralPayoutResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\WeddingEvents\WeddingEventResource;
use App\Filament\Widgets\PendingActivationsWidget;
use App\Filament\Widgets\PendingPayoutsWidget;
use App\Filament\Widgets\PlatformOpsStatsWidget;
use App\Filament\Widgets\PlatformStatsWidget;
use App\Filament\Widgets\PlatformVisitChartWidget;
use App\Filament\Widgets\RecentEnquiriesWidget;
use App\Filament\Widgets\UnverifiedUsersWidget;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class AdminDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            PlatformOpsStatsWidget::class,
            PendingActivationsWidget::class,
            UnverifiedUsersWidget::class,
            RecentEnquiriesWidget::class,
            PendingPayoutsWidget::class,
            PlatformStatsWidget::class,
            PlatformVisitChartWidget::class,
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('weddings')
                ->label('Weddings')
                ->icon('heroicon-o-heart')
                ->url(WeddingEventResource::getUrl('index')),
            Action::make('users')
                ->label('Users')
                ->icon('heroicon-o-user-group')
                ->url(UserResource::getUrl('index')),
            Action::make('payouts')
                ->label('Referral payouts')
                ->icon('heroicon-o-banknotes')
                ->url(ReferralPayoutResource::getUrl('index')),
        ];
    }
}

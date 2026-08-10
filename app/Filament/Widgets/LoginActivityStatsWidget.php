<?php

namespace App\Filament\Widgets;

use App\Support\AdminDashboardMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoginActivityStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Login activity';

    protected function getStats(): array
    {
        return [
            Stat::make('Successful logins', (string) AdminDashboardMetrics::successfulLoginsTodayCount())
                ->description('Today')
                ->icon('heroicon-o-arrow-right-end-on-rectangle'),
            Stat::make('Failed logins', (string) AdminDashboardMetrics::failedLoginsTodayCount())
                ->description('Today')
                ->icon('heroicon-o-shield-exclamation'),
        ];
    }
}

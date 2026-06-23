<?php

namespace App\Filament\Widgets;

use App\Models\LinkVisit;
use App\Models\WeddingEvent;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $baseQuery = LinkVisit::query();

        $totalViews = (clone $baseQuery)->count();

        $thisMonthViews = (clone $baseQuery)
            ->where('visited_at', '>=', now()->startOfMonth())
            ->count();

        $uniqueVisitorsThisMonth = (clone $baseQuery)
            ->where('visited_at', '>=', now()->startOfMonth())
            ->whereNotNull('ip_hash')
            ->distinct('ip_hash')
            ->count('ip_hash');

        $activeWeddings = WeddingEvent::query()
            ->where('is_active', true)
            ->count();

        return [
            Stat::make('Total link visits', (string) $totalViews)
                ->description('All weddings, all time')
                ->icon('heroicon-o-eye'),
            Stat::make('Visits this month', (string) $thisMonthViews)
                ->description('Across all weddings')
                ->icon('heroicon-o-calendar'),
            Stat::make('Unique visitors', (string) $uniqueVisitorsThisMonth)
                ->description('This month')
                ->icon('heroicon-o-user'),
            Stat::make('Active weddings', (string) $activeWeddings)
                ->description('Currently published')
                ->icon('heroicon-o-heart'),
        ];
    }
}

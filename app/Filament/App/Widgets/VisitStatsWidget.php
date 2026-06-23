<?php

namespace App\Filament\App\Widgets;

use App\LinkType;
use App\Models\LinkVisit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VisitStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding) {
            return [];
        }

        $baseQuery = LinkVisit::query()->where('wedding_event_id', $wedding->id);

        $totalViews = (clone $baseQuery)->count();

        $thisMonthViews = (clone $baseQuery)
            ->where('visited_at', '>=', now()->startOfMonth())
            ->count();

        $uniqueVisitorsThisMonth = (clone $baseQuery)
            ->where('visited_at', '>=', now()->startOfMonth())
            ->whereNotNull('ip_hash')
            ->distinct('ip_hash')
            ->count('ip_hash');

        $personalOpens = (clone $baseQuery)
            ->where('link_type', LinkType::Personal)
            ->count();

        return [
            Stat::make(__('app.stat_total_opens'), (string) $totalViews)
                ->description(__('app.stat_total_opens_desc'))
                ->icon('heroicon-o-eye'),
            Stat::make(__('app.stat_this_month'), (string) $thisMonthViews)
                ->description(__('app.stat_this_month_desc'))
                ->icon('heroicon-o-calendar'),
            Stat::make(__('app.stat_unique_visitors'), (string) $uniqueVisitorsThisMonth)
                ->description(__('app.stat_unique_visitors_desc'))
                ->icon('heroicon-o-user'),
            Stat::make(__('app.stat_personal_opens'), (string) $personalOpens)
                ->description(__('app.stat_personal_opens_desc'))
                ->icon('heroicon-o-link'),
        ];
    }
}

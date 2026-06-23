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
            Stat::make('Ukupno otvorenja', (string) $totalViews)
                ->description('Sva otvorenja pozivnice')
                ->icon('heroicon-o-eye'),
            Stat::make('Ovaj mjesec', (string) $thisMonthViews)
                ->description('Otvorenja ovog mjeseca')
                ->icon('heroicon-o-calendar'),
            Stat::make('Jedinstveni posjetitelji', (string) $uniqueVisitorsThisMonth)
                ->description('Ovaj mjesec')
                ->icon('heroicon-o-user'),
            Stat::make('Personalni linkovi', (string) $personalOpens)
                ->description('Otvorenja personalnih linkova')
                ->icon('heroicon-o-link'),
        ];
    }
}

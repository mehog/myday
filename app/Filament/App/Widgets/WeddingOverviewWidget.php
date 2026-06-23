<?php

namespace App\Filament\App\Widgets;

use App\RsvpStatus;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeddingOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding) {
            return [];
        }

        $guestCount = $wedding->guests()->count();
        $confirmed = $wedding->guests()->where('rsvp_status', RsvpStatus::Yes)->count();
        $responded = $wedding->guests()->whereNotNull('rsvp_status')->count();
        $responseRate = $guestCount > 0 ? round(($responded / $guestCount) * 100) : 0;
        $daysUntil = (int) now()->startOfDay()->diffInDays($wedding->wedding_date->copy()->startOfDay(), false);

        return [
            Stat::make(__('app.stat_guests'), (string) $guestCount)
                ->description(__('app.stat_guests_desc'))
                ->icon('heroicon-o-users'),
            Stat::make(__('app.stat_confirmed'), (string) $confirmed)
                ->description(__('app.stat_confirmed_desc'))
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make(__('app.stat_responded'), "{$responseRate}%")
                ->description(__('app.stat_responded_desc', ['responded' => $responded, 'total' => $guestCount]))
                ->icon('heroicon-o-chat-bubble-left-right'),
            Stat::make(
                __('app.stat_days_until'),
                $daysUntil >= 0 ? __('app.stat_days_value', ['days' => $daysUntil]) : __('app.stat_days_passed')
            )
                ->description($wedding->wedding_date->translatedFormat('d. F Y.'))
                ->icon('heroicon-o-calendar-days')
                ->color($daysUntil >= 0 ? 'primary' : 'gray'),
        ];
    }
}

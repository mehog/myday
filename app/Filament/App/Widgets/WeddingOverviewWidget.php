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
            Stat::make('Gosti', (string) $guestCount)
                ->description('Ukupno pozvanih gostiju')
                ->icon('heroicon-o-users'),
            Stat::make('Potvrdili', (string) $confirmed)
                ->description('Potvrdili dolazak')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Odgovorili', "{$responseRate}%")
                ->description("{$responded} od {$guestCount} gostiju")
                ->icon('heroicon-o-chat-bubble-left-right'),
            Stat::make('Do vjenčanja', $daysUntil >= 0 ? "{$daysUntil} dana" : 'Prošlo')
                ->description($wedding->wedding_date->translatedFormat('d. F Y.'))
                ->icon('heroicon-o-calendar-days')
                ->color($daysUntil >= 0 ? 'primary' : 'gray'),
        ];
    }
}

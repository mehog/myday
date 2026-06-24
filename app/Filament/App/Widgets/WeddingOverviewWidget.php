<?php

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\GuestMessagesResource;
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
        $confirmedGuests = $wedding->guests()->where('rsvp_status', RsvpStatus::Yes)->count();
        $plusOnes = $wedding->guests()
            ->where('rsvp_status', RsvpStatus::Yes)
            ->whereNotNull('plus_one_name')
            ->count();
        $confirmed = $confirmedGuests + $plusOnes;
        $responded = $wedding->guests()->whereNotNull('rsvp_status')->count();
        $responseRate = $guestCount > 0 ? round(($responded / $guestCount) * 100) : 0;
        $daysUntil = (int) now()->startOfDay()->diffInDays($wedding->wedding_date->copy()->startOfDay(), false);
        $messageCount = $wedding->guestMessages()->count();
        $unseenCount = $wedding->guestMessages()->whereNull('seen_at')->count();

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
            Stat::make(__('app.stat_messages'), (string) $messageCount)
                ->description($unseenCount > 0
                    ? __('app.stat_messages_unseen', ['count' => $unseenCount])
                    : __('app.stat_messages_desc'))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color($unseenCount > 0 ? 'warning' : null)
                ->url(GuestMessagesResource::getUrl()),
        ];
    }
}

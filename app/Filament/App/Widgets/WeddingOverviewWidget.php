<?php

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\GuestMessagesResource;
use App\Models\GuestChild;
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
        $plusOneInvitees = $wedding->guests()->where('plus_one_allowed', true)->count();
        $confirmedGuests = $wedding->guests()->where('rsvp_status', RsvpStatus::Yes)->count();
        $plusOnes = $wedding->guests()
            ->where('rsvp_status', RsvpStatus::Yes)
            ->whereNotNull('plus_one_name')
            ->count();
        $children = GuestChild::query()
            ->whereIn(
                'guest_id',
                $wedding->guests()->where('rsvp_status', RsvpStatus::Yes)->select('id'),
            )
            ->count();
        $confirmed = $confirmedGuests + $plusOnes + $children;
        $responded = $wedding->guests()->whereNotNull('rsvp_status')->count();
        $responseRate = $guestCount > 0 ? round(($responded / $guestCount) * 100) : 0;
        $daysUntil = (int) now()->startOfDay()->diffInDays($wedding->wedding_date->copy()->startOfDay(), false);
        $messageCount = $wedding->guestMessages()->count();
        $unseenCount = $wedding->guestMessages()->whereNull('seen_at')->count();

        $confirmedDescription = match (true) {
            $plusOnes > 0 && $children > 0 => __('app.stat_confirmed_desc_plus_ones_children', [
                'plus_ones' => $plusOnes,
                'children' => $children,
            ]),
            $plusOnes > 0 => __('app.stat_confirmed_desc_plus_ones', ['count' => $plusOnes]),
            $children > 0 => __('app.stat_confirmed_desc_children', ['count' => $children]),
            default => __('app.stat_confirmed_desc'),
        };

        return [
            Stat::make(__('app.stat_guests'), (string) $guestCount)
                ->description($plusOneInvitees > 0
                    ? __('app.stat_guests_desc_plus_ones', ['count' => $plusOneInvitees])
                    : __('app.stat_guests_desc'))
                ->icon('heroicon-o-users'),
            Stat::make(__('app.stat_confirmed'), (string) $confirmed)
                ->description($confirmedDescription)
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

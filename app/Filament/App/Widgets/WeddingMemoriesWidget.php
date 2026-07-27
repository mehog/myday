<?php

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\GuestMessagesResource;
use App\Filament\App\Resources\MyWeddingResource;
use App\GuestMessageType;
use App\Models\GuestChild;
use App\Models\GuestMessage;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use App\Support\MediaDisk;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class WeddingMemoriesWidget extends Widget
{
    protected string $view = 'filament.app.widgets.wedding-memories';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $wedding = auth()->user()?->weddingEvent;

        return $wedding instanceof WeddingEvent && $wedding->isArchived();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding instanceof WeddingEvent) {
            return [
                'wedding' => null,
            ];
        }

        $guestCount = $wedding->guests()->count();
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
        $responded = $wedding->guests()->whereNotNull('rsvp_status')->count();
        $daysSince = (int) $wedding->wedding_date->copy()->startOfDay()->diffInDays(now()->startOfDay());

        $textMessages = $wedding->guestMessages()
            ->where('type', GuestMessageType::Text)
            ->latest()
            ->limit(5)
            ->get();
        $audioMessages = $wedding->guestMessages()
            ->where('type', GuestMessageType::Audio)
            ->latest()
            ->limit(5)
            ->get();
        $photoMessages = $wedding->guestMessages()
            ->where('type', GuestMessageType::Photo)
            ->whereNotNull('file_paths')
            ->latest()
            ->limit(8)
            ->get();

        $photoPreviews = $this->photoPreviews($photoMessages);
        $photoCount = $wedding->guestMessages()
            ->where('type', GuestMessageType::Photo)
            ->whereNotNull('file_paths')
            ->get(['file_paths'])
            ->sum(fn (GuestMessage $message): int => count($message->file_paths ?? []));

        return [
            'wedding' => $wedding,
            'daysSince' => max(0, $daysSince),
            'guestCount' => $guestCount,
            'responded' => $responded,
            'confirmedGuests' => $confirmedGuests,
            'plusOnes' => $plusOnes,
            'children' => $children,
            'confirmedTotal' => $confirmedGuests + $plusOnes + $children,
            'scheduleItems' => $wedding->scheduleItems()->orderBy('sort_order')->get(),
            'textMessages' => $textMessages,
            'audioMessages' => $audioMessages,
            'textCount' => $wedding->guestMessages()->where('type', GuestMessageType::Text)->count(),
            'audioCount' => $wedding->guestMessages()->where('type', GuestMessageType::Audio)->count(),
            'photoCount' => $photoCount,
            'photoPreviews' => $photoPreviews,
            'messagesUrl' => GuestMessagesResource::getUrl(),
            'photosUrl' => GuestMessagesResource::getUrl('photos'),
            'photosDownloadUrl' => route('guest-messages.photos.download'),
            'weddingUrl' => MyWeddingResource::getUrl('edit', ['record' => $wedding]),
            'previewUrl' => $wedding->public_url,
        ];
    }

    /**
     * @param  Collection<int, GuestMessage>  $messages
     * @return list<array{url: string, sender_name: string}>
     */
    protected function photoPreviews(Collection $messages): array
    {
        $previews = [];

        foreach ($messages as $message) {
            foreach ($message->file_paths ?? [] as $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }

                $url = MediaDisk::url($path);

                if (! filled($url)) {
                    continue;
                }

                $previews[] = [
                    'url' => $url,
                    'sender_name' => $message->sender_name,
                ];

                if (count($previews) >= 8) {
                    return $previews;
                }
            }
        }

        return $previews;
    }
}

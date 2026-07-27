<?php

namespace App\Filament\App\Resources\GuestMessagesResource\Pages;

use App\Filament\App\Resources\GuestMessagesResource;
use App\GuestMessageType;
use App\Models\GuestMessage;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListGuestMessages extends ListRecords
{
    protected static string $resource = GuestMessagesResource::class;

    public function getTitle(): string
    {
        return __('app.guest_messages_title');
    }

    protected function getHeaderActions(): array
    {
        $hasPhotos = GuestMessage::query()
            ->where('wedding_event_id', auth()->user()?->weddingEvent?->id ?? 0)
            ->where('type', GuestMessageType::Photo)
            ->exists();

        return [
            Action::make('viewAllPhotos')
                ->label(__('app.guest_messages_view_all_photos'))
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->url(GuestMessagesResource::getUrl('photos'))
                ->visible($hasPhotos),
            Action::make('downloadPhotos')
                ->label(__('app.guest_messages_download_photos'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('guest-messages.photos.download'))
                ->visible($hasPhotos),
        ];
    }
}

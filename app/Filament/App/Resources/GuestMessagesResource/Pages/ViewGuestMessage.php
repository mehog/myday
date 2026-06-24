<?php

namespace App\Filament\App\Resources\GuestMessagesResource\Pages;

use App\Filament\App\Resources\GuestMessagesResource;
use App\GuestMessageType;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewGuestMessage extends ViewRecord
{
    protected static string $resource = GuestMessagesResource::class;

    public function getTitle(): string
    {
        return __('app.guest_messages_detail_title', ['name' => $this->record->sender_name]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPhotos')
                ->label(__('app.guest_messages_download_photos'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn (): string => route('guest-messages.photos.download', ['message' => $this->record->id]))
                ->visible(fn (): bool => $this->record->type === GuestMessageType::Photo
                    && ! empty($this->record->file_paths)),
            Action::make('back')
                ->label(__('app.guest_messages_back'))
                ->icon('heroicon-o-arrow-left')
                ->url(GuestMessagesResource::getUrl())
                ->color('gray'),
        ];
    }
}

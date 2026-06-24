<?php

namespace App\Filament\App\Resources\PushNotificationsResource\Pages;

use App\Filament\App\Resources\PushNotificationsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPushNotifications extends ListRecords
{
    protected static string $resource = PushNotificationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('app.push_notifications_send')),
        ];
    }
}

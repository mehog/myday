<?php

namespace App\Filament\App\Resources\PushNotificationsResource\Pages;

use App\Filament\App\Resources\PushNotificationsResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditPushNotification extends EditRecord
{
    protected static string $resource = PushNotificationsResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('app.push_notifications_compose'))
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label(__('app.push_notifications_field_title'))
                            ->required()
                            ->maxLength(50),
                        Textarea::make('body')
                            ->label(__('app.push_notifications_field_body'))
                            ->required()
                            ->maxLength(120)
                            ->rows(3),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotificationTitle(__('app.push_notifications_deleted')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PushNotificationsResource::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('app.push_notifications_updated');
    }
}

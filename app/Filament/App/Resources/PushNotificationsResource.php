<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PushNotificationsResource\Pages\CreatePushNotification;
use App\Filament\App\Resources\PushNotificationsResource\Pages\EditPushNotification;
use App\Filament\App\Resources\PushNotificationsResource\Pages\ListPushNotifications;
use App\Models\PushNotificationLog;
use App\PushNotificationRecipientType;
use App\PushNotificationStatus;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class PushNotificationsResource extends Resource
{
    protected static ?string $model = PushNotificationLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?string $slug = 'push-notifications';

    protected static ?int $navigationSort = 30;

    public static function getNavigationLabel(): string
    {
        return __('app.nav_push_notifications');
    }

    public static function getModelLabel(): string
    {
        return __('app.push_notifications_title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.nav_push_notifications');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->weddingEvent !== null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('app.push_notifications_compose'))
                    ->columnSpanFull()
                    ->schema([
                        Placeholder::make('subscriber_count')
                            ->label('')
                            ->content(function (): HtmlString {
                                $count = static::subscriberCount();

                                return new HtmlString(
                                    '<p class="text-sm text-gray-500 dark:text-gray-400">'
                                    .e(__('app.push_notifications_subscriber_count', ['count' => $count]))
                                    .'</p>'
                                );
                            }),
                        TextInput::make('title')
                            ->label(__('app.push_notifications_field_title'))
                            ->required()
                            ->maxLength(50),
                        Textarea::make('body')
                            ->label(__('app.push_notifications_field_body'))
                            ->required()
                            ->maxLength(120)
                            ->rows(3),
                        Radio::make('recipient_type')
                            ->label(__('app.push_notifications_field_recipients'))
                            ->options(collect(PushNotificationRecipientType::cases())->mapWithKeys(
                                fn (PushNotificationRecipientType $type) => [$type->value => $type->label()]
                            ))
                            ->default(PushNotificationRecipientType::All->value)
                            ->required()
                            ->live(),
                        CheckboxList::make('selected_guest_ids')
                            ->label(__('app.push_notifications_field_select_guests'))
                            ->options(fn (): array => static::subscriberOptions())
                            ->visible(fn (callable $get): bool => $get('recipient_type') === PushNotificationRecipientType::Selected->value)
                            ->required(fn (callable $get): bool => $get('recipient_type') === PushNotificationRecipientType::Selected->value)
                            ->columns(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('app.push_notifications_field_title'))
                    ->searchable(),
                TextColumn::make('body')
                    ->label(__('app.push_notifications_field_body'))
                    ->limit(50),
                TextColumn::make('sent_to_count')
                    ->label(__('app.push_notifications_sent_to'))
                    ->numeric(),
                TextColumn::make('status')
                    ->label(__('app.push_notifications_status'))
                    ->badge()
                    ->color(fn (PushNotificationStatus $state): string => match ($state) {
                        PushNotificationStatus::Queued => 'warning',
                        PushNotificationStatus::Sent => 'success',
                        PushNotificationStatus::Failed => 'danger',
                    })
                    ->formatStateUsing(fn (PushNotificationStatus $state): string => $state->label()),
                TextColumn::make('recipient_type')
                    ->label(__('app.push_notifications_field_recipients'))
                    ->badge()
                    ->formatStateUsing(fn (PushNotificationRecipientType $state): string => $state->label()),
                TextColumn::make('sent_at')
                    ->label(__('app.push_notifications_sent_at'))
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('failed_reason')
                    ->label(__('app.push_notifications_failed_reason'))
                    ->limit(50)
                    ->placeholder('—')
                    ->tooltip(fn (PushNotificationLog $record): ?string => $record->failed_reason)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('app.push_notifications_created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('app.push_notifications_edit')),
                DeleteAction::make()
                    ->successNotificationTitle(__('app.push_notifications_deleted')),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $weddingEventId = auth()->user()?->weddingEvent?->id;

        return parent::getEloquentQuery()
            ->when($weddingEventId, fn (Builder $query) => $query->where('wedding_event_id', $weddingEventId))
            ->when(! $weddingEventId, fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPushNotifications::route('/'),
            'create' => CreatePushNotification::route('/create'),
            'edit' => EditPushNotification::route('/{record}/edit'),
        ];
    }

    public static function subscriberCount(): int
    {
        $weddingEvent = auth()->user()?->weddingEvent;

        if (! $weddingEvent) {
            return 0;
        }

        return $weddingEvent->guests()
            ->whereHas('pushSubscriptions')
            ->count();
    }

    /**
     * @return array<int, string>
     */
    public static function subscriberOptions(): array
    {
        $weddingEvent = auth()->user()?->weddingEvent;

        if (! $weddingEvent) {
            return [];
        }

        return $weddingEvent->guests()
            ->whereHas('pushSubscriptions')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn ($guest) => [
                $guest->id => $guest->name.' ('.($guest->rsvp_status?->label() ?? __('app.push_notifications_rsvp_pending')).')',
            ])
            ->all();
    }
}

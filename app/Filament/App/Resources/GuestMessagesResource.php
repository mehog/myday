<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\GuestMessagesResource\Pages\ListGuestMessages;
use App\Filament\App\Resources\GuestMessagesResource\Pages\ViewGuestMessage;
use App\GuestMessageType;
use App\Models\GuestMessage;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GuestMessagesResource extends Resource
{
    protected static ?string $model = GuestMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?string $slug = 'poruke-gostiju';

    protected static ?int $navigationSort = 40;

    public static function getNavigationLabel(): string
    {
        return __('app.nav_guest_messages');
    }

    public static function getModelLabel(): string
    {
        return __('app.guest_messages_model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.guest_messages_plural_label');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->weddingEvent !== null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('app.guest_messages_col_message'))
                    ->columnSpanFull()
                    ->collapsible()
                    ->visible(fn (GuestMessage $record): bool => $record->type === GuestMessageType::Text)
                    ->schema([
                        TextEntry::make('content')
                            ->hiddenLabel()
                            ->prose(),
                    ]),
                Section::make(__('app.guest_messages_col_photo'))
                    ->columnSpanFull()
                    ->collapsible()
                    ->visible(fn (GuestMessage $record): bool => $record->type === GuestMessageType::Photo)
                    ->schema([
                        TextEntry::make('photo_count')
                            ->hiddenLabel()
                            ->getStateUsing(fn (GuestMessage $record): string => __('app.guest_messages_col_photo_count', [
                                'count' => count($record->file_paths ?? []),
                            ])),
                        ViewEntry::make('file_paths')
                            ->hiddenLabel()
                            ->view('filament.app.resources.guest-messages.photo-gallery')
                            ->columnSpanFull(),
                    ]),
                Section::make(__('app.guest_messages_col_audio'))
                    ->columnSpanFull()
                    ->collapsible()
                    ->visible(fn (GuestMessage $record): bool => $record->type === GuestMessageType::Audio)
                    ->schema([
                        TextEntry::make('file_path')
                            ->hiddenLabel()
                            ->formatStateUsing(fn (): string => __('app.guest_messages_listen'))
                            ->url(fn (GuestMessage $record): ?string => $record->fileUrl())
                            ->openUrlInNewTab(),
                    ]),
                Section::make(__('app.guest_messages_detail_info'))
                    ->columnSpan(fn (GuestMessage $record): int|string => $record->hasFingerprint() ? 1 : 'full')
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('sender_name')
                            ->label(__('app.guest_messages_col_sender')),
                        TextEntry::make('type')
                            ->label(__('app.guest_messages_col_type'))
                            ->badge()
                            ->formatStateUsing(fn (?GuestMessageType $state): ?string => $state?->label())
                            ->color(fn (?GuestMessageType $state): string => match ($state) {
                                GuestMessageType::Text => 'info',
                                GuestMessageType::Audio => 'warning',
                                GuestMessageType::Photo => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')
                            ->label(__('app.guest_messages_col_sent_at'))
                            ->dateTime(),
                    ]),
                Section::make(__('app.guest_messages_device_section'))
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn (GuestMessage $record): bool => $record->hasFingerprint())
                    ->columns(2)
                    ->schema([
                        TextEntry::make('deviceSummary')
                            ->label(__('app.guest_messages_sent_from'))
                            ->getStateUsing(fn (GuestMessage $record): ?string => $record->deviceSummary())
                            ->placeholder('—'),
                        TextEntry::make('invitation_device')
                            ->label(__('app.guest_messages_opened_from'))
                            ->getStateUsing(function (GuestMessage $record): ?string {
                                $visit = $record->guest?->latestPersonalLinkVisit;

                                if (! $visit) {
                                    return null;
                                }

                                $parts = array_filter([
                                    $visit->browser,
                                    $visit->os,
                                    $visit->device_type,
                                ]);

                                return $parts === [] ? null : implode(' / ', $parts);
                            })
                            ->placeholder('—'),
                        TextEntry::make('visit_match')
                            ->label(__('app.guest_messages_visit_match_label'))
                            ->badge()
                            ->getStateUsing(fn (GuestMessage $record): string => $record->visitMatch()->label())
                            ->color(fn (GuestMessage $record): string => $record->visitMatch()->color())
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('sender_name')
                    ->label(__('app.guest_messages_col_sender'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('app.guest_messages_col_type'))
                    ->badge()
                    ->formatStateUsing(fn (?GuestMessageType $state): ?string => $state?->label())
                    ->color(fn (?GuestMessageType $state): string => match ($state) {
                        GuestMessageType::Text => 'info',
                        GuestMessageType::Audio => 'warning',
                        GuestMessageType::Photo => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('content')
                    ->label(__('app.guest_messages_col_message'))
                    ->limit(80)
                    ->placeholder('—')
                    ->wrap()
                    ->visible(fn ($record): bool => $record?->type === GuestMessageType::Text),
                ImageColumn::make('file_paths')
                    ->label(__('app.guest_messages_col_photo'))
                    ->disk(config('filesystems.media_disk'))
                    ->getStateUsing(fn (GuestMessage $record): ?string => $record->file_paths[0] ?? null)
                    ->visible(fn ($record): bool => $record?->type === GuestMessageType::Photo),
                TextColumn::make('photo_count')
                    ->label(__('app.guest_messages_col_photo'))
                    ->getStateUsing(fn (GuestMessage $record): string => __('app.guest_messages_col_photo_count', [
                        'count' => count($record->file_paths ?? []),
                    ]))
                    ->visible(fn ($record): bool => $record?->type === GuestMessageType::Photo),
                TextColumn::make('file_path')
                    ->label(__('app.guest_messages_col_audio'))
                    ->formatStateUsing(fn (): string => __('app.guest_messages_listen'))
                    ->url(fn ($record): ?string => $record?->type === GuestMessageType::Audio ? $record->fileUrl() : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->visible(fn ($record): bool => $record?->type === GuestMessageType::Audio),
                TextColumn::make('visit_match')
                    ->label(__('app.guest_messages_col_origin'))
                    ->badge()
                    ->getStateUsing(fn (GuestMessage $record): string => $record->visitMatch()->label())
                    ->color(fn (GuestMessage $record): string => $record->visitMatch()->color())
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('app.guest_messages_col_sent_at'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('app.guest_messages_filter_type'))
                    ->options(collect(GuestMessageType::cases())
                        ->mapWithKeys(fn (GuestMessageType $type) => [$type->value => $type->label()])),
                SelectFilter::make('sender_name')
                    ->label(__('app.guest_messages_filter_sender'))
                    ->options(fn (): array => GuestMessage::query()
                        ->where('wedding_event_id', auth()->user()?->weddingEvent?->id ?? 0)
                        ->distinct()
                        ->orderBy('sender_name')
                        ->pluck('sender_name', 'sender_name')
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->emptyStateHeading(__('app.guest_messages_empty_heading'))
            ->emptyStateDescription(__('app.guest_messages_empty_desc'));
    }

    public static function getEloquentQuery(): Builder
    {
        $weddingEventId = auth()->user()?->weddingEvent?->id;

        return parent::getEloquentQuery()
            ->with(['guest.latestPersonalLinkVisit'])
            ->when($weddingEventId, fn (Builder $query) => $query->where('wedding_event_id', $weddingEventId))
            ->when(! $weddingEventId, fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuestMessages::route('/'),
            'view' => ViewGuestMessage::route('/{record}'),
        ];
    }
}

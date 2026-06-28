<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\GuestMessageType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GuestMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'guestMessages';

    protected static ?string $title = 'Guest messages';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('guest.name')
                    ->label('Guest')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?GuestMessageType $state): ?string => $state?->label()),
                TextColumn::make('content')
                    ->label('Message')
                    ->limit(60)
                    ->placeholder('—')
                    ->wrap()
                    ->visible(fn ($record): bool => $record?->type === GuestMessageType::Text),
                ImageColumn::make('file_paths')
                    ->label('Photo')
                    ->disk(config('filesystems.media_disk'))
                    ->getStateUsing(fn ($record) => $record->file_paths[0] ?? null)
                    ->visible(fn ($record): bool => $record?->type === GuestMessageType::Photo),
                TextColumn::make('file_path')
                    ->label('Audio')
                    ->formatStateUsing(fn (): string => 'Listen')
                    ->url(fn ($record): ?string => $record->type === GuestMessageType::Audio ? $record->fileUrl() : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->visible(fn ($record): bool => $record?->type === GuestMessageType::Audio),
                TextColumn::make('created_at')
                    ->label('Sent at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(collect(GuestMessageType::cases())->mapWithKeys(fn (GuestMessageType $type) => [$type->value => $type->label()])),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->emptyStateHeading('No guest messages yet')
            ->emptyStateDescription('Messages from guests will appear here once they use the contact page.');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Filament\Resources\WeddingEvents\RelationManagers\Concerns\ShowsRelationshipCountBadge;
use App\Models\PushNotificationLog;
use App\PushNotificationRecipientType;
use App\PushNotificationStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PushNotificationLogsRelationManager extends RelationManager
{
    use ShowsRelationshipCountBadge;

    protected static string $relationship = 'pushNotificationLogs';

    protected static ?string $title = 'Push notifications';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
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
                        PushNotificationStatus::Scheduled => 'info',
                        PushNotificationStatus::Queued => 'warning',
                        PushNotificationStatus::Sent => 'success',
                        PushNotificationStatus::Failed => 'danger',
                    })
                    ->formatStateUsing(fn (PushNotificationStatus $state): string => $state->label()),
                TextColumn::make('scheduled_at')
                    ->label(__('app.push_notifications_scheduled_at'))
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateIcon('heroicon-o-bell')
            ->emptyStateHeading('No push notifications yet')
            ->emptyStateDescription('Notifications sent or scheduled from the couple app will appear here.');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

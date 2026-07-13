<?php

namespace App\Filament\Resources\Guests\Tables;

use App\Filament\Imports\GuestImporter;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount('linkVisits')
                ->withMax('linkVisits as last_visited_at', 'visited_at'))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('weddingEvent.slug')
                    ->label('Wedding')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('rsvp_status')
                    ->badge()
                    ->formatStateUsing(fn (?RsvpStatus $state) => $state?->label() ?? 'Pending'),
                TextColumn::make('rsvp_responded_at')
                    ->dateTime()
                    ->placeholder('—'),
                TextColumn::make('plus_one_name')
                    ->label('Companion name')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('personal_url')
                    ->label('Personal link')
                    ->copyable()
                    ->copyMessage('Link copied')
                    ->toggleable(),
                TextColumn::make('link_visits_count')
                    ->label('Opens')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_visited_at')
                    ->label('Last opened')
                    ->since()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('wedding_event_id')
                    ->label('Wedding')
                    ->searchable()
                    ->options(fn (): array => WeddingEvent::query()
                        ->orderByDesc('id')
                        ->pluck('slug', 'id')
                        ->all()),
                SelectFilter::make('rsvp_status')
                    ->label('RSVP')
                    ->options([
                        'pending' => 'Pending',
                        RsvpStatus::Yes->value => RsvpStatus::Yes->label(),
                        RsvpStatus::No->value => RsvpStatus::No->label(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        if ($value === 'pending') {
                            return $query->whereNull('rsvp_status');
                        }

                        return $query->where('rsvp_status', $value);
                    }),
                TernaryFilter::make('invite_sent_at')
                    ->label('Invite sent')
                    ->placeholder('All')
                    ->trueLabel('Sent')
                    ->falseLabel('Not sent')
                    ->nullable(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->headerActions([
                Action::make('importCsv')
                    ->label('Import CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Select::make('wedding_event_id')
                            ->label('Wedding')
                            ->options(WeddingEvent::query()->pluck('slug', 'id'))
                            ->required(),
                        FileUpload::make('file')
                            ->label('CSV file')
                            ->disk('local')
                            ->directory('temp/csv-imports')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $event = WeddingEvent::query()->findOrFail($data['wedding_event_id']);
                        $contents = Storage::disk('local')->get($data['file']);
                        $count = GuestImporter::importFromContents($event, $contents);

                        Storage::disk('local')->delete($data['file']);

                        Notification::make()
                            ->title("Imported {$count} guests")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Filament\Imports\GuestImporter;
use App\Models\Guest;
use App\RsvpStatus;
use App\Support\Clipboard;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GuestsRelationManager extends RelationManager
{
    protected static string $relationship = 'guests';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('weddingEvent'))
            ->recordTitleAttribute('name')
            ->columns([
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
                TextColumn::make('personal_url')
                    ->label('Personal link')
                    ->limit(40)
                    ->tooltip(fn (Guest $record): string => $record->personal_url)
                    ->copyable()
                    ->copyMessage('Link copied'),
            ])
            ->headerActions([
                CreateAction::make(),
                Action::make('importCsv')
                    ->label('Import CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('file')
                            ->label('CSV file')
                            ->disk('local')
                            ->directory('temp/csv-imports')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $contents = \Illuminate\Support\Facades\Storage::disk('local')->get($data['file']);
                        $count = GuestImporter::importFromContents($this->getOwnerRecord(), $contents);

                        \Illuminate\Support\Facades\Storage::disk('local')->delete($data['file']);

                        Notification::make()
                            ->title("Imported {$count} guests")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('copyPersonalLink')
                    ->label('Copy link')
                    ->icon('heroicon-o-clipboard')
                    ->color('gray')
                    ->alpineClickHandler(fn (Guest $record): string => Clipboard::alpineCopy($record->personal_url)),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

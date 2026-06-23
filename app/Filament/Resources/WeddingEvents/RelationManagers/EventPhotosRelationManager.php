<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventPhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'eventPhotos';

    protected static ?string $title = 'Fotografije';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('Fotografija')
                    ->image()
                    ->directory('event-photos')
                    ->disk(config('filesystems.media_disk'))
                    ->required(),
                TextInput::make('title')
                    ->label('Naslov')
                    ->maxLength(255)
                    ->placeholder('npr. Dvorana, Vrt, Parking')
                    ->helperText('Opcionalno. Prikazuje se ispod fotografije na pozivnici.'),
                TextInput::make('sort_order')
                    ->label('Redosljed')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('path')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label('Fotografija')
                    ->disk(config('filesystems.media_disk')),
                TextColumn::make('title')
                    ->label('Naslov')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('Redosljed')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-photo')
            ->emptyStateHeading('Još nema fotografija')
            ->emptyStateDescription('Dodajte prvu fotografiju lokacije ili vašeg para.')
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}

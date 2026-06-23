<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScheduleItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'scheduleItems';

    protected static ?string $title = 'Raspored';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TimePicker::make('time')
                    ->label('Vrijeme')
                    ->required()
                    ->seconds(false),
                TextInput::make('title')
                    ->label('Naziv')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Opis')
                    ->rows(3)
                    ->columnSpanFull(),
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
            ->recordTitleAttribute('title')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('time')
                    ->label('Vrijeme')
                    ->time('H:i'),
                TextColumn::make('title')
                    ->label('Naziv')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Opis')
                    ->limit(50),
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
            ->emptyStateIcon('heroicon-o-clock')
            ->emptyStateHeading('Još nema stavki u rasporedu')
            ->emptyStateDescription('Dodajte prvi događaj vašeg dana — npr. ceremonija, koktel ili večera.')
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}

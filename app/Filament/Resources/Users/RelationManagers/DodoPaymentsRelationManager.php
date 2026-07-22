<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DodoPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'dodoPayments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Dodo payments';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('plan_tier')
                    ->label('Plan')
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—'),
                TextColumn::make('pricing_region')
                    ->label('Region')
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—'),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state, $record) => trim(($state ?? '').' '.($record->currency ?? ''))),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—'),
                TextColumn::make('dodo_payment_id')
                    ->label('Payment ID')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([])
            ->headerActions([])
            ->toolbarActions([]);
    }
}

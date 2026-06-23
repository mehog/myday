<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\LinkType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LinkVisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'linkVisits';

    protected static ?string $title = 'Link visits';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('visited_at', 'desc')
            ->columns([
                TextColumn::make('link_type')
                    ->label('Link type')
                    ->badge()
                    ->formatStateUsing(fn (?LinkType $state): ?string => $state?->label()),
                TextColumn::make('device_type')
                    ->label('Device')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('browser')
                    ->label('Browser')
                    ->placeholder('—'),
                TextColumn::make('os')
                    ->label('OS')
                    ->placeholder('—'),
                TextColumn::make('referer')
                    ->label('Referer')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('visited_at')
                    ->label('Visited at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('link_type')
                    ->label('Link type')
                    ->options(collect(LinkType::cases())->mapWithKeys(fn (LinkType $type) => [$type->value => $type->label()])),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateIcon('heroicon-o-eye')
            ->emptyStateHeading('No link visits yet')
            ->emptyStateDescription('Visits are recorded when guests open public or personal invitation links.');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Filament\Resources\WeddingEvents\RelationManagers\Concerns\ShowsRelationshipCountBadge;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BudgetItemsRelationManager extends RelationManager
{
    use ShowsRelationshipCountBadge;

    protected static string $relationship = 'budgetItems';

    protected static ?string $title = 'Budget items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label(__('budget.item_name'))
                    ->searchable(),
                TextColumn::make('category')
                    ->label(__('budget.category'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—'),
                TextColumn::make('calculation_type')
                    ->label(__('budget.calculation_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—'),
                TextColumn::make('amount')
                    ->label(__('budget.amount'))
                    ->numeric(decimalPlaces: 2),
                IconColumn::make('is_paid')
                    ->label(__('budget.paid'))
                    ->boolean(),
                TextColumn::make('notes')
                    ->label(__('budget.notes'))
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateIcon('heroicon-o-calculator')
            ->emptyStateHeading(__('budget.empty_title'))
            ->emptyStateDescription(__('budget.empty_body'));
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

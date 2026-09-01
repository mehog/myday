<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Filament\Resources\WeddingEvents\RelationManagers\Concerns\ShowsRelationshipCountBadge;
use App\Models\WeddingTask;
use App\WeddingTaskPriority;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    use ShowsRelationshipCountBadge;

    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Checklist';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label(__('checklist.field_title'))
                    ->getStateUsing(fn (WeddingTask $record): string => $record->displayTitle())
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search): void {
                            $q->where('title', 'like', "%{$search}%")
                                ->orWhere('system_key', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('period')
                    ->label('Period')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—'),
                TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn (?WeddingTaskPriority $state): string => match ($state) {
                        WeddingTaskPriority::High => 'danger',
                        WeddingTaskPriority::Low => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (?WeddingTaskPriority $state): string => $state?->label() ?? '—'),
                TextColumn::make('due_date')
                    ->label(__('checklist.field_due_date'))
                    ->date()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Completed at')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('system_key')
                    ->label('System key')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->emptyStateHeading(__('checklist.empty_heading'))
            ->emptyStateDescription(__('checklist.empty_description'));
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

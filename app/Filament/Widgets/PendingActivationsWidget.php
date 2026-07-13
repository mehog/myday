<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\WeddingEvents\WeddingEventResource;
use App\Models\WeddingEvent;
use App\Support\AdminDashboardMetrics;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingActivationsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pending activations')
            ->query(fn (): Builder => AdminDashboardMetrics::pendingActivationsQuery()->limit(8))
            ->defaultSort('wedding_date')
            ->paginated(false)
            ->columns([
                TextColumn::make('couple_names')
                    ->label('Couple')
                    ->searchable(['groom_name', 'bride_name']),
                TextColumn::make('wedding_date')
                    ->label('Wedding date')
                    ->date()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Owner email')
                    ->placeholder('—'),
                TextColumn::make('days_until')
                    ->label('Days until')
                    ->state(function (WeddingEvent $record): string {
                        if ($record->wedding_date->isPast()) {
                            return 'Past';
                        }

                        return (string) now()->startOfDay()->diffInDays($record->wedding_date->copy()->startOfDay());
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (WeddingEvent $record): string => WeddingEventResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No pending activations')
            ->emptyStateDescription('All non-demo weddings are published.');
    }
}

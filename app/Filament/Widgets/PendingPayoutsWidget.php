<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ReferralPayouts\ReferralPayoutResource;
use App\Models\ReferralPayout;
use App\Support\AdminDashboardMetrics;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingPayoutsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pending payouts')
            ->query(fn (): Builder => AdminDashboardMetrics::pendingPayoutsQuery()->limit(8))
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->columns([
                TextColumn::make('referrer.name')
                    ->label('Referrer')
                    ->placeholder('—'),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn (ReferralPayout $record): string => $record->currency),
                TextColumn::make('period')
                    ->label('Period'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (ReferralPayout $record): string => ReferralPayoutResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No pending payouts')
            ->emptyStateDescription('All referral payouts are up to date.');
    }
}

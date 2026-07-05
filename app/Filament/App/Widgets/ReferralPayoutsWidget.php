<?php

namespace App\Filament\App\Widgets;

use App\Models\ReferralPayout;
use App\ReferralPayoutStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ReferralPayoutsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $referrerId = auth()->id();

        return $table
            ->heading(__('referrals.payouts_heading'))
            ->query(fn (): Builder => ReferralPayout::query()
                ->when($referrerId, fn (Builder $query) => $query->where('referrer_id', $referrerId))
                ->when(! $referrerId, fn (Builder $query) => $query->whereRaw('1 = 0'))
                ->orderByDesc('created_at'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('period')
                    ->label(__('referrals.col_period'))
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('referrals.col_amount'))
                    ->money(fn (ReferralPayout $record): string => $record->currency)
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('referrals.col_payout_status'))
                    ->badge()
                    ->formatStateUsing(fn (?ReferralPayoutStatus $state): ?string => $state?->label())
                    ->color(fn (?ReferralPayoutStatus $state): string => match ($state) {
                        ReferralPayoutStatus::Paid => 'success',
                        ReferralPayoutStatus::Pending => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')
                    ->label(__('referrals.col_paid_at'))
                    ->dateTime()
                    ->placeholder('—'),
                TextColumn::make('payment_proof')
                    ->label(__('referrals.col_payment_proof'))
                    ->formatStateUsing(fn (): string => __('referrals.view_proof'))
                    ->url(fn (ReferralPayout $record): ?string => $record->paymentProofUrl())
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->placeholder('—'),
                TextColumn::make('payment_link')
                    ->label(__('referrals.col_payment_link'))
                    ->formatStateUsing(fn (): string => __('referrals.open_link'))
                    ->url(fn (ReferralPayout $record): ?string => $record->payment_link)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->placeholder('—'),
            ])
            ->emptyStateHeading(__('referrals.payouts_empty_heading'))
            ->emptyStateDescription(__('referrals.payouts_empty_desc'));
    }
}

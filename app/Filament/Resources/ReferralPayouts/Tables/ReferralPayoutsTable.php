<?php

namespace App\Filament\Resources\ReferralPayouts\Tables;

use App\ReferralPayoutStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReferralPayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('referrer.name')
                    ->label(__('referrals.admin_field_referrer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period')
                    ->label(__('referrals.col_period'))
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('referrals.col_amount'))
                    ->money(fn ($record): string => $record->currency)
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
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('referrals.admin_col_created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('referrals.col_payout_status'))
                    ->options(collect(ReferralPayoutStatus::cases())
                        ->mapWithKeys(fn (ReferralPayoutStatus $status) => [$status->value => $status->label()])),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}

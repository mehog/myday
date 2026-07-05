<?php

namespace App\Filament\App\Widgets;

use App\Models\Referral;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MyReferralsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $referrerId = auth()->id();

        return $table
            ->heading(__('referrals.my_referrals_heading'))
            ->query(fn (): Builder => Referral::query()
                ->with(['user.weddingEvent'])
                ->when($referrerId, fn (Builder $query) => $query->where('referrer_id', $referrerId))
                ->when(! $referrerId, fn (Builder $query) => $query->whereRaw('1 = 0'))
                ->orderByDesc('created_at'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('referrals.col_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label(__('referrals.col_email'))
                    ->searchable(),
                TextColumn::make('user.weddingEvent.couple_names')
                    ->label(__('referrals.col_wedding'))
                    ->placeholder(__('referrals.no_wedding')),
                TextColumn::make('status')
                    ->label(__('referrals.col_status'))
                    ->badge()
                    ->getStateUsing(function (Referral $record): string {
                        $wedding = $record->user?->weddingEvent;

                        if ($wedding === null) {
                            return __('referrals.status_no_wedding');
                        }

                        if ($wedding->is_active) {
                            return __('referrals.status_active');
                        }

                        return __('referrals.status_pending_payment');
                    })
                    ->color(function (Referral $record): string {
                        $wedding = $record->user?->weddingEvent;

                        if ($wedding === null) {
                            return 'gray';
                        }

                        return $wedding->is_active ? 'success' : 'warning';
                    }),
                TextColumn::make('created_at')
                    ->label(__('referrals.col_referred_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->emptyStateHeading(__('referrals.referrals_empty_heading'))
            ->emptyStateDescription(__('referrals.referrals_empty_desc'));
    }
}

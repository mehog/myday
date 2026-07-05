<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\Referral;
use App\Models\User;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'referrals';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('referrals.admin_referrals_tab');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['user.weddingEvent']))
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
                TextColumn::make('commission_rate')
                    ->label(__('referrals.admin_col_commission_rate'))
                    ->getStateUsing(function (): string {
                        /** @var User $owner */
                        $owner = $this->getOwnerRecord();

                        return number_format($owner->referralFeePercentage(), 0).'%';
                    }),
                TextColumn::make('created_at')
                    ->label(__('referrals.col_referred_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->description(function (): string {
                /** @var User $owner */
                $owner = $this->getOwnerRecord();
                $referrals = $owner->referrals()->with('user.weddingEvent')->get();

                $active = $referrals->filter(
                    fn (Referral $referral): bool => (bool) $referral->user?->weddingEvent?->is_active
                )->count();

                $pending = $referrals->filter(
                    fn (Referral $referral): bool => $referral->user?->weddingEvent !== null
                        && ! $referral->user->weddingEvent->is_active
                )->count();

                return __('referrals.admin_referrals_summary', [
                    'active' => $active,
                    'pending' => $pending,
                    'fee' => number_format($owner->referralFeePercentage(), 0),
                ]);
            })
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateHeading(__('referrals.referrals_empty_heading'))
            ->emptyStateDescription(__('referrals.referrals_empty_desc'));
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

<?php

namespace App\Filament\Resources\ReferralPayouts\Schemas;

use App\Models\ReferralPayout;
use App\ReferralPayoutStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReferralPayoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('referrals.admin_section_payout'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('referrer.name')
                            ->label(__('referrals.admin_field_referrer')),
                        TextEntry::make('period')
                            ->label(__('referrals.col_period')),
                        TextEntry::make('amount')
                            ->label(__('referrals.col_amount'))
                            ->money(fn (ReferralPayout $record): string => $record->currency),
                        TextEntry::make('status')
                            ->label(__('referrals.col_payout_status'))
                            ->badge()
                            ->formatStateUsing(fn (?ReferralPayoutStatus $state): ?string => $state?->label())
                            ->color(fn (?ReferralPayoutStatus $state): string => match ($state) {
                                ReferralPayoutStatus::Paid => 'success',
                                ReferralPayoutStatus::Pending => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('paid_at')
                            ->label(__('referrals.col_paid_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('payment_link')
                            ->label(__('referrals.col_payment_link'))
                            ->url(fn (ReferralPayout $record): ?string => $record->payment_link)
                            ->openUrlInNewTab()
                            ->placeholder('—'),
                        TextEntry::make('payment_proof')
                            ->label(__('referrals.col_payment_proof'))
                            ->formatStateUsing(fn (): string => __('referrals.view_proof'))
                            ->url(fn (ReferralPayout $record): ?string => $record->paymentProofUrl())
                            ->openUrlInNewTab()
                            ->placeholder('—'),
                        TextEntry::make('notes')
                            ->label(__('referrals.admin_field_notes'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make(__('referrals.admin_section_referrer_payout_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('referrer.paypal_email')
                            ->label(__('referrals.paypal_email'))
                            ->placeholder('—'),
                        TextEntry::make('referrer.bank_account_info')
                            ->label(__('referrals.bank_account_info'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('referrer.referral_fee_percentage')
                            ->label(__('referrals.admin_field_fee_override'))
                            ->formatStateUsing(fn (?string $state, ReferralPayout $record): string => $state !== null
                                ? number_format((float) $state, 2).'%'
                                : __('referrals.admin_fee_default', ['fee' => number_format($record->referrer?->referralFeePercentage() ?? config('referral.default_fee', 10), 0)])),
                    ]),
            ]);
    }
}

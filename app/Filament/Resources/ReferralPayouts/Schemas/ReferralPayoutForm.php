<?php

namespace App\Filament\Resources\ReferralPayouts\Schemas;

use App\ReferralPayoutStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReferralPayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('referrals.admin_section_payout'))
                    ->columns(2)
                    ->schema([
                        Select::make('referrer_id')
                            ->label(__('referrals.admin_field_referrer'))
                            ->relationship('referrer', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => request()->integer('referrer_id') ?: null)
                            ->required(),
                        TextInput::make('period')
                            ->label(__('referrals.col_period'))
                            ->placeholder('2026-06')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('amount')
                            ->label(__('referrals.col_amount'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01),
                        TextInput::make('currency')
                            ->label(__('referrals.admin_field_currency'))
                            ->default('EUR')
                            ->required()
                            ->maxLength(3),
                        Select::make('status')
                            ->label(__('referrals.col_payout_status'))
                            ->options(collect(ReferralPayoutStatus::cases())
                                ->mapWithKeys(fn (ReferralPayoutStatus $status) => [$status->value => $status->label()]))
                            ->default(ReferralPayoutStatus::Pending->value)
                            ->required()
                            ->live(),
                        DateTimePicker::make('paid_at')
                            ->label(__('referrals.col_paid_at'))
                            ->visible(fn ($get): bool => $get('status') === ReferralPayoutStatus::Paid->value),
                        FileUpload::make('payment_proof')
                            ->label(__('referrals.col_payment_proof'))
                            ->disk(config('filesystems.media_disk'))
                            ->directory('referral-payouts')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10240)
                            ->columnSpanFull(),
                        TextInput::make('payment_link')
                            ->label(__('referrals.col_payment_link'))
                            ->url()
                            ->maxLength(2048)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label(__('referrals.admin_field_notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

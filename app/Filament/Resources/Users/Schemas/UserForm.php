<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\Locale;
use App\Traits\Referrable;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                Toggle::make('is_admin')
                    ->label('Administrator')
                    ->helperText('Admins access /admin. Customers access /app.')
                    ->default(false),
                Select::make('locale')
                    ->label(__('locale.label'))
                    ->options(Locale::options())
                    ->nullable(),
                Placeholder::make('email_verification_status')
                    ->label('Email verification')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->content(function (?User $record): string {
                        if (! $record?->hasVerifiedEmail()) {
                            return 'Not verified';
                        }

                        return 'Verified at '.$record->email_verified_at->format('Y-m-d H:i');
                    }),
                Section::make(__('referrals.admin_section_referral_link'))
                    ->visible(fn (string $operation, ?User $record): bool => $operation === 'edit' && ! ($record?->is_admin ?? false))
                    ->schema([
                        TextInput::make('referral_code')
                            ->label(__('referrals.admin_field_referral_code'))
                            ->maxLength(50)
                            ->helperText(__('referrals.admin_field_referral_code_helper'))
                            ->rules(fn (?User $record): array => [
                                'nullable',
                                'string',
                                'min:3',
                                'max:50',
                                'regex:/^[a-zA-Z0-9_-]+$/',
                                Rule::unique('referrals', 'referral_code')->ignore($record?->referralAccount?->id),
                            ]),
                        Placeholder::make('referral_link_preview')
                            ->label(__('referrals.your_link_label'))
                            ->content(function (Get $get): string {
                                $code = Referrable::normalizeReferralCode((string) $get('referral_code'));

                                if ($code === '') {
                                    return '—';
                                }

                                $prefix = config('referral.route_prefix') ?: 'ref';

                                return url('/'.$prefix.'/'.$code);
                            }),
                    ]),
                TextInput::make('referral_fee_percentage')
                    ->label(__('referrals.admin_field_fee_override'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->suffix('%')
                    ->placeholder((string) config('referral.default_fee', 10))
                    ->helperText(__('referrals.admin_fee_override_helper', [
                        'default' => config('referral.default_fee', 10),
                    ])),
                TextInput::make('paypal_email')
                    ->label(__('referrals.paypal_email'))
                    ->email()
                    ->maxLength(255),
                Textarea::make('bank_account_info')
                    ->label(__('referrals.bank_account_info'))
                    ->rows(3)
                    ->maxLength(2000),
            ]);
    }
}

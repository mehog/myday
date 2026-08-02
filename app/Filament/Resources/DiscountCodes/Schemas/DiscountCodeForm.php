<?php

namespace App\Filament\Resources\DiscountCodes\Schemas;

use App\DiscountType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DiscountCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('discounts.section_code'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label(__('discounts.field_code'))
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state !== null ? strtoupper(trim($state)) : null),
                        TextInput::make('name')
                            ->label(__('discounts.field_name'))
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label(__('discounts.field_type'))
                            ->options(collect(DiscountType::cases())
                                ->mapWithKeys(fn (DiscountType $type) => [$type->value => $type->label()]))
                            ->default(DiscountType::Percentage->value)
                            ->required()
                            ->live(),
                        TextInput::make('amount')
                            ->label(__('discounts.field_amount'))
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->helperText(fn (callable $get): string => $get('type') === DiscountType::Flat->value
                                ? __('discounts.helper_amount_flat')
                                : __('discounts.helper_amount_percentage')),
                        Select::make('currency')
                            ->label(__('discounts.field_currency'))
                            ->options([
                                'EUR' => 'EUR',
                                'BAM' => 'BAM',
                            ])
                            ->visible(fn (callable $get): bool => $get('type') === DiscountType::Flat->value)
                            ->required(fn (callable $get): bool => $get('type') === DiscountType::Flat->value),
                        TextInput::make('dodo_discount_id')
                            ->label(__('discounts.field_dodo_discount_id'))
                            ->maxLength(255),
                        DateTimePicker::make('starts_at')
                            ->label(__('discounts.field_starts_at'))
                            ->seconds(false),
                        DateTimePicker::make('expires_at')
                            ->label(__('discounts.field_expires_at'))
                            ->seconds(false),
                        Toggle::make('is_active')
                            ->label(__('discounts.field_is_active'))
                            ->default(true),
                        Textarea::make('notes')
                            ->label(__('discounts.field_notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

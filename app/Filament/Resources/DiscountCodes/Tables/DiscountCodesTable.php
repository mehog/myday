<?php

namespace App\Filament\Resources\DiscountCodes\Tables;

use App\DiscountEmailRecipientStatus;
use App\DiscountType;
use App\Filament\Resources\DiscountEmailCampaigns\DiscountEmailCampaignResource;
use App\Models\DiscountCode;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DiscountCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label(__('discounts.field_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label(__('discounts.field_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('discounts.field_type'))
                    ->badge()
                    ->formatStateUsing(fn (?DiscountType $state): ?string => $state?->label()),
                TextColumn::make('amount')
                    ->label(__('discounts.field_amount'))
                    ->formatStateUsing(fn ($state, DiscountCode $record): string => $record->discountLabel()),
                IconColumn::make('is_active')
                    ->label(__('discounts.field_is_active'))
                    ->boolean(),
                TextColumn::make('expires_at')
                    ->label(__('discounts.field_expires_at'))
                    ->dateTime()
                    ->placeholder(__('discounts.expires_never'))
                    ->sortable(),
                TextColumn::make('campaigns_count')
                    ->counts('campaigns')
                    ->label(__('discounts.field_campaigns_count')),
                TextColumn::make('emails_sent')
                    ->label(__('discounts.field_emails_sent'))
                    ->getStateUsing(fn (DiscountCode $record): int => $record->recipients()
                        ->where('discount_email_recipients.status', DiscountEmailRecipientStatus::Sent)
                        ->count()),
                TextColumn::make('created_at')
                    ->label(__('discounts.col_created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('discounts.field_is_active')),
            ])
            ->recordActions([
                Action::make('sendEmail')
                    ->label(__('discounts.action_send_email'))
                    ->icon('heroicon-o-envelope')
                    ->url(fn (DiscountCode $record): string => DiscountEmailCampaignResource::getUrl('create', [
                        'discount_code_id' => $record->id,
                    ])),
                EditAction::make(),
            ]);
    }
}

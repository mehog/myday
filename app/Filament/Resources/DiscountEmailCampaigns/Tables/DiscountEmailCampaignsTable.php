<?php

namespace App\Filament\Resources\DiscountEmailCampaigns\Tables;

use App\DiscountEmailAudience;
use App\DiscountEmailCampaignStatus;
use App\Models\DiscountEmailCampaign;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DiscountEmailCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('discountCode.code')
                    ->label(__('discounts.field_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label(__('discounts.field_template'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label(__('discounts.field_subject'))
                    ->getStateUsing(fn (DiscountEmailCampaign $record): string => $record->renderedSubject())
                    ->limit(40),
                TextColumn::make('audience')
                    ->label(__('discounts.field_audience'))
                    ->formatStateUsing(fn (?DiscountEmailAudience $state): ?string => $state?->label()),
                TextColumn::make('status')
                    ->label(__('discounts.field_status'))
                    ->badge()
                    ->formatStateUsing(fn (?DiscountEmailCampaignStatus $state): ?string => $state?->label())
                    ->color(fn (?DiscountEmailCampaignStatus $state): string => match ($state) {
                        DiscountEmailCampaignStatus::Sent => 'success',
                        DiscountEmailCampaignStatus::Sending => 'info',
                        DiscountEmailCampaignStatus::Failed => 'danger',
                        DiscountEmailCampaignStatus::Cancelled => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('emails_sent')
                    ->label(__('discounts.field_emails_sent'))
                    ->getStateUsing(fn (DiscountEmailCampaign $record): int => $record->sentRecipientsCount()),
                TextColumn::make('sent_at')
                    ->label(__('discounts.field_sent_at'))
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('discounts.col_created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('discounts.field_status'))
                    ->options(collect(DiscountEmailCampaignStatus::cases())
                        ->mapWithKeys(fn (DiscountEmailCampaignStatus $status) => [$status->value => $status->label()])),
                SelectFilter::make('audience')
                    ->label(__('discounts.field_audience'))
                    ->options(collect(DiscountEmailAudience::cases())
                        ->mapWithKeys(fn (DiscountEmailAudience $audience) => [$audience->value => $audience->label()])),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}

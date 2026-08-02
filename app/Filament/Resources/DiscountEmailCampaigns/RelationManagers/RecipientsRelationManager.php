<?php

namespace App\Filament\Resources\DiscountEmailCampaigns\RelationManagers;

use App\DiscountEmailRecipientStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RecipientsRelationManager extends RelationManager
{
    protected static string $relationship = 'recipients';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('discounts.section_recipients');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('discounts.field_name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('discounts.field_email'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('locale')
                    ->label(__('discounts.field_locale'))
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label(__('discounts.field_status'))
                    ->badge()
                    ->formatStateUsing(fn (?DiscountEmailRecipientStatus $state): ?string => $state?->label())
                    ->color(fn (?DiscountEmailRecipientStatus $state): string => match ($state) {
                        DiscountEmailRecipientStatus::Sent => 'success',
                        DiscountEmailRecipientStatus::Failed => 'danger',
                        DiscountEmailRecipientStatus::Skipped => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('sent_at')
                    ->label(__('discounts.field_sent_at'))
                    ->dateTime()
                    ->placeholder('—'),
                TextColumn::make('error')
                    ->label(__('discounts.field_error'))
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('discounts.field_status'))
                    ->options(collect(DiscountEmailRecipientStatus::cases())
                        ->mapWithKeys(fn (DiscountEmailRecipientStatus $status) => [$status->value => $status->label()])),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}

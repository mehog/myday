<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\DiscountEmailRecipientStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DiscountEmailsRelationManager extends RelationManager
{
    protected static string $relationship = 'discountEmailRecipients';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('discounts.user_relation_title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('campaign.discountCode.code')
                    ->label(__('discounts.field_code'))
                    ->placeholder('—'),
                TextColumn::make('campaign.subject')
                    ->label(__('discounts.field_subject'))
                    ->limit(40),
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
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}

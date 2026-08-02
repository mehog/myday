<?php

namespace App\Filament\Resources\DiscountEmailTemplates\Tables;

use App\Models\DiscountEmailTemplate;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DiscountEmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('discounts.field_name'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('discounts.field_is_active'))
                    ->boolean(),
                TextColumn::make('campaigns_count')
                    ->counts('campaigns')
                    ->label(__('discounts.field_campaigns_count')),
                TextColumn::make('updated_at')
                    ->label(__('discounts.col_created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('discounts.field_is_active')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, DiscountEmailTemplate $record): void {
                        if ($record->isInUse()) {
                            Notification::make()
                                ->title(__('discounts.delete_template_in_use'))
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }
}

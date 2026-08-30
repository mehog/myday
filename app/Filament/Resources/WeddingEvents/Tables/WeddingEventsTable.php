<?php

namespace App\Filament\Resources\WeddingEvents\Tables;

use App\Support\MediaDisk;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WeddingEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount('linkVisits')
                ->withMax('linkVisits as last_visited_at', 'visited_at'))
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('hero_image')
                    ->label('Cover')
                    ->disk(MediaDisk::name())
                    ->height(56)
                    ->width(56)
                    ->square()
                    ->placeholder('—'),
                TextColumn::make('couple_names')
                    ->label('Couple')
                    ->searchable(['groom_name', 'bride_name']),
                TextColumn::make('motto')
                    ->label('Motto')
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->motto)
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Slug copied')
                    ->toggleable(),
                TextColumn::make('wedding_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('theme')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('link_mode')
                    ->badge()
                    ->toggleable(),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->toggleable(),
                ToggleColumn::make('is_demo')
                    ->label('Demo')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_marketing')
                    ->label('Marketing')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('link_visits_count')
                    ->label('Views')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('last_visited_at')
                    ->label('Last opened')
                    ->since()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_demo')
                    ->label('Demo')
                    ->placeholder('All')
                    ->trueLabel('Demo only')
                    ->falseLabel('Hide demos')
                    ->boolean()
                    ->default(false),
                TernaryFilter::make('is_marketing')
                    ->label('Marketing')
                    ->placeholder('All')
                    ->trueLabel('Marketing only')
                    ->falseLabel('Hide marketing')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

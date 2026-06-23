<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScheduleItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'scheduleItems';

    protected static ?string $title = null;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('schedule.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TimePicker::make('time')
                    ->label($this->trans('field_time'))
                    ->required()
                    ->seconds(false),
                TextInput::make('title')
                    ->label($this->trans('field_title'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label($this->trans('field_description'))
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label($this->trans('field_sort_order'))
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('time')
                    ->label($this->trans('field_time'))
                    ->time('H:i'),
                TextColumn::make('title')
                    ->label($this->trans('field_title'))
                    ->searchable(),
                TextColumn::make('description')
                    ->label($this->trans('field_description'))
                    ->limit(50),
                TextColumn::make('sort_order')
                    ->label($this->trans('field_sort_order'))
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-clock')
            ->emptyStateHeading($this->trans('empty_heading'))
            ->emptyStateDescription($this->trans('empty_description'))
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }

    protected function trans(string $key, array $replace = []): string
    {
        return __("schedule.{$key}", $replace);
    }
}

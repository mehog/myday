<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Filament\Resources\WeddingEvents\RelationManagers\Concerns\ShowsRelationshipCountBadge;
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
use Illuminate\Database\Eloquent\Model;

class ScheduleItemsRelationManager extends RelationManager
{
    use ShowsRelationshipCountBadge;

    protected static string $relationship = 'scheduleItems';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
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
            ->reorderable($this->coupleSetupLocked() ? null : 'sort_order')
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
                CreateAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked()),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked()),
                DeleteAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => ! $this->coupleSetupLocked()),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-clock')
            ->emptyStateHeading($this->trans('empty_heading'))
            ->emptyStateDescription($this->trans('empty_description'))
            ->emptyStateActions([
                CreateAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked()),
            ]);
    }

    protected function coupleSetupLocked(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'app'
            && $this->getOwnerRecord()->isArchived();
    }

    protected function trans(string $key, array $replace = []): string
    {
        return __("schedule.{$key}", $replace);
    }
}

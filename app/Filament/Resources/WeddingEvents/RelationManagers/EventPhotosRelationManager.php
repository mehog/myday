<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Filament\Resources\WeddingEvents\RelationManagers\Concerns\ShowsRelationshipCountBadge;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EventPhotosRelationManager extends RelationManager
{
    use ShowsRelationshipCountBadge;

    protected static string $relationship = 'eventPhotos';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('photos.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label($this->trans('field_photo'))
                    ->image()
                    ->directory('event-photos')
                    ->disk(config('filesystems.media_disk'))
                    ->required(),
                TextInput::make('title')
                    ->label($this->trans('field_title'))
                    ->maxLength(255)
                    ->placeholder($this->trans('field_title_placeholder'))
                    ->helperText($this->trans('field_title_helper')),
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
            ->recordTitleAttribute('path')
            ->defaultSort('sort_order')
            ->reorderable($this->coupleSetupLocked() ? null : 'sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label($this->trans('field_photo'))
                    ->disk(config('filesystems.media_disk')),
                TextColumn::make('title')
                    ->label($this->trans('field_title'))
                    ->placeholder('—')
                    ->searchable(),
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
            ->emptyStateIcon('heroicon-o-photo')
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
        return __("photos.{$key}", $replace);
    }
}

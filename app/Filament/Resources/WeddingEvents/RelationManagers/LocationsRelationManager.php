<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Models\WeddingEvent;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'locations';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('locations.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label($this->trans('field_label'))
                    ->helperText($this->trans('field_label_helper'))
                    ->maxLength(255),
                TextInput::make('name')
                    ->label($this->trans('field_name'))
                    ->maxLength(255)
                    ->required(),
                TextInput::make('address')
                    ->label($this->trans('field_address'))
                    ->maxLength(255)
                    ->columnSpanFull(),
                Toggle::make('is_primary')
                    ->label($this->trans('field_is_primary'))
                    ->helperText($this->trans('field_is_primary_helper'))
                    ->default(fn (): bool => ! $this->getOwnerRecord()->locations()->exists()),
                TextInput::make('sort_order')
                    ->label($this->trans('field_sort_order'))
                    ->numeric()
                    ->default(fn (): int => ((int) $this->getOwnerRecord()->locations()->max('sort_order')) + 1)
                    ->required(),
                Section::make($this->trans('section_coordinates'))
                    ->description($this->trans('coordinates_description'))
                    ->collapsed()
                    ->collapsible()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('lat')
                            ->label($this->trans('field_lat'))
                            ->numeric()
                            ->step(0.0000001),
                        TextInput::make('lng')
                            ->label($this->trans('field_lng'))
                            ->numeric()
                            ->step(0.0000001),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order')
            ->reorderable($this->coupleSetupLocked() ? null : 'sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label($this->trans('field_label'))
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('name')
                    ->label($this->trans('field_name'))
                    ->searchable(),
                TextColumn::make('address')
                    ->label($this->trans('field_address'))
                    ->limit(40)
                    ->placeholder('—'),
                IconColumn::make('is_primary')
                    ->label($this->trans('field_is_primary'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label($this->trans('field_sort_order'))
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked())
                    ->after(fn () => $this->normalizePrimaryAndLegacy()),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked())
                    ->after(fn () => $this->normalizePrimaryAndLegacy()),
                DeleteAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked())
                    ->after(fn () => $this->normalizePrimaryAndLegacy()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => ! $this->coupleSetupLocked())
                        ->after(fn () => $this->normalizePrimaryAndLegacy()),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-map-pin')
            ->emptyStateHeading($this->trans('empty_heading'))
            ->emptyStateDescription($this->trans('empty_description'))
            ->emptyStateActions([
                CreateAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked())
                    ->after(fn () => $this->normalizePrimaryAndLegacy()),
            ]);
    }

    protected function normalizePrimaryAndLegacy(): void
    {
        /** @var WeddingEvent $event */
        $event = $this->getOwnerRecord();
        $locations = $event->locations()->orderBy('sort_order')->orderBy('id')->get();

        if ($locations->isEmpty()) {
            $event->forceFill([
                'location_name' => null,
                'location_address' => null,
                'location_lat' => null,
                'location_lng' => null,
            ])->save();

            return;
        }

        $primary = $locations->firstWhere('is_primary', true) ?? $locations->first();

        foreach ($locations as $location) {
            $shouldBePrimary = $location->is($primary);

            if ((bool) $location->is_primary !== $shouldBePrimary) {
                $location->forceFill(['is_primary' => $shouldBePrimary])->save();
            }
        }

        $event->syncLegacyLocationFromPrimary();
    }

    protected function coupleSetupLocked(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'app'
            && $this->getOwnerRecord()->isArchived();
    }

    protected function trans(string $key, array $replace = []): string
    {
        return __("locations.{$key}", $replace);
    }
}

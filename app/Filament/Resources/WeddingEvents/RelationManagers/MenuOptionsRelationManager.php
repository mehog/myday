<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Filament\Resources\WeddingEvents\RelationManagers\Concerns\ShowsRelationshipCountBadge;
use App\Models\WeddingMenuOption;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MenuOptionsRelationManager extends RelationManager
{
    use ShowsRelationshipCountBadge;

    protected static string $relationship = 'menuOptions';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('menu.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label($this->trans('field_label'))
                    ->required(fn (?WeddingMenuOption $record): bool => $record?->isCustom() ?? true)
                    ->maxLength(255)
                    ->visible(fn (?WeddingMenuOption $record): bool => $record?->isCustom() ?? true)
                    ->dehydrated(fn (?WeddingMenuOption $record): bool => $record?->isCustom() ?? true),
                TextInput::make('platform_display')
                    ->label($this->trans('field_platform'))
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn (?WeddingMenuOption $record): ?string => $record?->platform_key?->label())
                    ->visible(fn (?WeddingMenuOption $record): bool => $record?->isPlatform() ?? false),
                Toggle::make('is_visible')
                    ->label($this->trans('field_is_visible'))
                    ->helperText($this->trans('field_is_visible_helper'))
                    ->default(true)
                    ->required(),
                TextInput::make('sort_order')
                    ->label($this->trans('field_sort_order'))
                    ->numeric()
                    ->default(fn (): int => ((int) $this->getOwnerRecord()->menuOptions()->max('sort_order')) + 1)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->defaultSort('sort_order')
            ->reorderable($this->coupleSetupLocked() ? null : 'sort_order')
            ->columns([
                TextColumn::make('display_label')
                    ->label($this->trans('field_label'))
                    ->getStateUsing(fn (WeddingMenuOption $record): string => $record->displayLabel())
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($inner) use ($search): void {
                            $inner->where('label', 'like', "%{$search}%")
                                ->orWhere('platform_key', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('type')
                    ->label('')
                    ->badge()
                    ->getStateUsing(fn (WeddingMenuOption $record): string => $record->isPlatform()
                        ? $this->trans('platform_badge')
                        : $this->trans('custom_badge'))
                    ->color(fn (WeddingMenuOption $record): string => $record->isPlatform() ? 'gray' : 'primary'),
                IconColumn::make('is_visible')
                    ->label($this->trans('field_is_visible'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label($this->trans('field_sort_order'))
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['platform_key'] = null;
                        $data['is_visible'] = $data['is_visible'] ?? true;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked()),
                DeleteAction::make()
                    ->visible(fn (WeddingMenuOption $record): bool => ! $this->coupleSetupLocked() && $record->isCustom())
                    ->before(function (DeleteAction $action, WeddingMenuOption $record): void {
                        if ($record->isPlatform()) {
                            Notification::make()
                                ->title($this->trans('cannot_delete_platform'))
                                ->danger()
                                ->send();
                            $action->halt();

                            return;
                        }

                        if ($this->menuOptionIsInUse($record)) {
                            Notification::make()
                                ->title($this->trans('cannot_delete_in_use'))
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => ! $this->coupleSetupLocked())
                        ->action(function ($records): void {
                            $records->each(function (WeddingMenuOption $record): void {
                                if ($record->isPlatform() || $this->menuOptionIsInUse($record)) {
                                    return;
                                }

                                $record->delete();
                            });
                        }),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-cake')
            ->emptyStateHeading($this->trans('empty_heading'))
            ->emptyStateDescription($this->trans('empty_description'))
            ->emptyStateActions([
                CreateAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['platform_key'] = null;

                        return $data;
                    }),
            ]);
    }

    protected function menuOptionIsInUse(WeddingMenuOption $record): bool
    {
        return DB::table('guests')
            ->where(function ($query) use ($record): void {
                $query->where('menu_option_id', $record->id)
                    ->orWhere('plus_one_menu_option_id', $record->id);
            })
            ->exists()
            || DB::table('guest_children')
                ->where('menu_option_id', $record->id)
                ->exists();
    }

    protected function coupleSetupLocked(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'app'
            && $this->getOwnerRecord()->isArchived();
    }

    protected function trans(string $key, array $replace = []): string
    {
        return __("menu.{$key}", $replace);
    }
}

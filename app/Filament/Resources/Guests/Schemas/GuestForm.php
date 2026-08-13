<?php

namespace App\Filament\Resources\Guests\Schemas;

use App\GuestLabel;
use App\Models\WeddingEvent;
use App\Support\Locale;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class GuestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('wedding_event_id')
                    ->label('Wedding')
                    ->options(WeddingEvent::query()->pluck('slug', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Select::make('invitation_locale')
                    ->label('Invitation language override')
                    ->helperText('Leave empty to use the wedding default invitation language.')
                    ->options(Locale::options())
                    ->placeholder('Use wedding default')
                    ->nullable()
                    ->native(false)
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null),
                Toggle::make('plus_one_allowed')
                    ->label('Plus one')
                    ->default(false),
                CheckboxList::make('labels')
                    ->label(__('guests.field_labels'))
                    ->helperText(__('guests.field_labels_helper'))
                    ->options(GuestLabel::options())
                    ->columns(2)
                    ->bulkToggleable()
                    ->afterStateHydrated(function (CheckboxList $component, mixed $state): void {
                        $component->state(
                            Collection::wrap($state)
                                ->map(fn (mixed $label): ?string => $label instanceof GuestLabel ? $label->value : (is_string($label) ? $label : null))
                                ->filter()
                                ->values()
                                ->all()
                        );
                    })
                    ->dehydrateStateUsing(fn (?array $state): ?array => filled($state) ? array_values($state) : null),
            ]);
    }
}

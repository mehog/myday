<?php

namespace App\Filament\Resources\Guests\Schemas;

use App\Models\WeddingEvent;
use App\Support\Locale;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

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
            ]);
    }
}

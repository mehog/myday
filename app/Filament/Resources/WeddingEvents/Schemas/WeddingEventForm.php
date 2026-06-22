<?php

namespace App\Filament\Resources\WeddingEvents\Schemas;

use App\Models\User;
use App\InvitationTheme;
use App\LinkMode;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WeddingEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Couple')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Customer account')
                            ->options(fn () => User::query()->where('is_admin', false)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->helperText('Assign a customer who can manage this wedding at /app'),
                        TextInput::make('groom_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('bride_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Used in the public URL: /e/{slug}'),
                        DateTimePicker::make('wedding_date')
                            ->required()
                            ->native(false),
                    ]),
                Section::make('Design')
                    ->columns(2)
                    ->schema([
                        Select::make('theme')
                            ->options(collect(InvitationTheme::cases())->mapWithKeys(fn (InvitationTheme $theme) => [$theme->value => $theme->label()]))
                            ->required()
                            ->native(false),
                        Select::make('link_mode')
                            ->options(collect(LinkMode::cases())->mapWithKeys(fn (LinkMode $mode) => [$mode->value => $mode->label()]))
                            ->required()
                            ->native(false),
                        FileUpload::make('hero_image')
                            ->image()
                            ->directory('hero-images')
                            ->disk(config('filesystems.media_disk'))
                            ->columnSpanFull(),
                        TextInput::make('music_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Zalijepite YouTube link pjesme (npr. https://www.youtube.com/watch?v=... ili https://youtu.be/...)')
                            ->columnSpanFull(),
                    ]),
                Section::make('Location')
                    ->columns(2)
                    ->schema([
                        TextInput::make('location_name')
                            ->maxLength(255),
                        TextInput::make('location_address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('location_lat')
                            ->numeric()
                            ->step(0.0000001),
                        TextInput::make('location_lng')
                            ->numeric()
                            ->step(0.0000001),
                    ]),
                Section::make('RSVP')
                    ->schema([
                        DatePicker::make('rsvp_deadline')
                            ->native(false),
                        Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                    ]),
                Section::make('Invite message')
                    ->schema([
                        Textarea::make('send_message')
                            ->label('Message template')
                            ->helperText('Use {name} for guest name and {link} for personal invite link.')
                            ->placeholder("Dragi {name}, sa radošću vas pozivamo na naše vjenčanje!\nVaš link za potvrdu dolaska: {link}")
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

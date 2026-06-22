<?php

namespace App\Filament\App\Schemas;

use App\InvitationTheme;
use App\LinkMode;
use App\Models\WeddingEvent;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MyWeddingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Par')
                    ->columnSpanFull()
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('groom_name')
                            ->label('Ime mladoženje')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('bride_name')
                            ->label('Ime mlade')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Link pozivnice')
                            ->readOnly()
                            ->dehydrated(false)
                            ->helperText(fn (?WeddingEvent $record): ?string => $record
                                ? 'Vaš link: '.$record->public_url
                                : null)
                            ->columnSpanFull(),
                        DateTimePicker::make('wedding_date')
                            ->label('Datum i vrijeme vjenčanja')
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),
                    ]),
                Section::make('Dizajn')
                    ->columnSpanFull()
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Select::make('theme')
                            ->label('Tema')
                            ->options(collect(InvitationTheme::cases())->mapWithKeys(fn (InvitationTheme $theme) => [$theme->value => $theme->label()]))
                            ->required()
                            ->native(false),
                        Select::make('link_mode')
                            ->label('Način dijeljenja')
                            ->options(collect(LinkMode::cases())->mapWithKeys(fn (LinkMode $mode) => [$mode->value => $mode->label()]))
                            ->required()
                            ->native(false),
                        FileUpload::make('hero_image')
                            ->label('Naslovna fotografija')
                            ->image()
                            ->directory('hero-images')
                            ->disk(config('filesystems.media_disk')),
                        TextInput::make('music_url')
                            ->label('YouTube pjesma')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Zalijepite YouTube link pjesme (npr. https://www.youtube.com/watch?v=... ili https://youtu.be/...)'),
                    ]),
                Section::make('Lokacija')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('location_name')
                            ->label('Naziv lokacije')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('location_address')
                            ->label('Adresa')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('location_lat')
                            ->label('Lat')
                            ->numeric()
                            ->step(0.0000001),
                        TextInput::make('location_lng')
                            ->label('Lng')
                            ->numeric()
                            ->step(0.0000001),
                    ]),
                Section::make('RSVP')
                    ->collapsible()
                    ->schema([
                        DatePicker::make('rsvp_deadline')
                            ->label('Rok za potvrdu dolaska')
                            ->native(false),
                        Textarea::make('send_message')
                            ->label('Poruka za goste')
                            ->helperText('Koristite {name} za ime gosta i {link} za personalizovani link.')
                            ->placeholder("Dragi {name}, sa radošću vas pozivamo na naše vjenčanje!\nVaš link za potvrdu dolaska: {link}")
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\App\Schemas;

use App\InvitationTheme;
use App\LinkMode;
use App\Models\WeddingEvent;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MyWeddingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('app.section_couple'))
                    ->columnSpanFull()
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('groom_name')
                            ->label(__('app.groom_name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('bride_name')
                            ->label(__('app.bride_name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label(__('app.invitation_link'))
                            ->readOnly()
                            ->dehydrated(false)
                            ->helperText(fn (?WeddingEvent $record): ?string => $record
                                ? __('app.your_link').$record->public_url
                                : null)
                            ->columnSpanFull(),
                        DateTimePicker::make('wedding_date')
                            ->label(__('app.wedding_datetime'))
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('app.section_design'))
                    ->columnSpanFull()
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Select::make('theme')
                            ->label(__('app.theme'))
                            ->options(collect(InvitationTheme::cases())->mapWithKeys(fn (InvitationTheme $theme) => [$theme->value => $theme->label()]))
                            ->required()
                            ->native(false),
                        Select::make('link_mode')
                            ->label(__('app.share_mode'))
                            ->options(collect(LinkMode::cases())->mapWithKeys(fn (LinkMode $mode) => [$mode->value => $mode->label()]))
                            ->required()
                            ->native(false),
                        FileUpload::make('hero_image')
                            ->label(__('app.hero_image'))
                            ->image()
                            ->directory('hero-images')
                            ->disk(config('filesystems.media_disk')),
                        TextInput::make('music_url')
                            ->label(__('app.youtube_song'))
                            ->url()
                            ->maxLength(500)
                            ->helperText(__('app.youtube_helper')),
                        Textarea::make('motto')
                            ->label(__('app.motto'))
                            ->helperText(__('app.motto_helper'))
                            ->maxLength(300)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('app.section_location'))
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('location_name')
                            ->label(__('app.location_name'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('location_address')
                            ->label(__('app.location_address'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Section::make(__('app.section_coordinates'))
                            ->description(__('app.coordinates_description'))
                            ->collapsed()
                            ->collapsible()
                            ->columns(2)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('location_lat')
                                    ->label(__('app.latitude'))
                                    ->numeric()
                                    ->step(0.0000001),
                                TextInput::make('location_lng')
                                    ->label(__('app.longitude'))
                                    ->numeric()
                                    ->step(0.0000001),
                            ]),
                    ]),
                Section::make(__('app.section_rsvp'))
                    ->collapsible()
                    ->schema([
                        DatePicker::make('rsvp_deadline')
                            ->label(__('app.rsvp_deadline'))
                            ->native(false),
                        Textarea::make('send_message')
                            ->label(__('app.guest_message'))
                            ->helperText(__('app.guest_message_helper'))
                            ->placeholder(__('app.guest_message_placeholder'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

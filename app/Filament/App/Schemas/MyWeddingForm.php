<?php

namespace App\Filament\App\Schemas;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\Models\WeddingEvent;
use App\Support\Locale;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MyWeddingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('app.section_couple'))
                    ->description(fn (?WeddingEvent $record): ?string => $record?->isArchived()
                        ? __('app.wedding_archived_readonly')
                        : null)
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextInput::make('groom_name')
                            ->label(__('app.groom_name'))
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
                        TextInput::make('bride_name')
                            ->label(__('app.bride_name'))
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
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
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false))
                            ->helperText(fn (?WeddingEvent $record): ?string => $record?->isArchived()
                                ? __('app.wedding_date_locked')
                                : null)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('app.section_design'))
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        Select::make('theme')
                            ->label(__('app.theme'))
                            ->options(collect(InvitationTheme::cases())->mapWithKeys(fn (InvitationTheme $theme) => [$theme->value => $theme->label()]))
                            ->required()
                            ->native(false)
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
                        Select::make('template')
                            ->label(__('app.template'))
                            ->options(collect(InvitationTemplate::cases())->mapWithKeys(fn (InvitationTemplate $template) => [$template->value => $template->label()]))
                            ->required()
                            ->native(false)
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
                        Select::make('reveal_animation')
                            ->label(__('app.reveal_animation'))
                            ->options(collect(InvitationReveal::cases())->mapWithKeys(fn (InvitationReveal $reveal) => [$reveal->value => $reveal->label()]))
                            ->nullable()
                            ->placeholder(__('app.reveal_none'))
                            ->native(false)
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
                        FileUpload::make('hero_image')
                            ->label(__('app.hero_image'))
                            ->image()
                            ->directory('hero-images')
                            ->disk(config('filesystems.media_disk'))
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
                        TextInput::make('music_url')
                            ->label(__('app.youtube_song'))
                            ->url()
                            ->maxLength(500)
                            ->helperText(__('app.youtube_helper'))
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
                        Textarea::make('motto')
                            ->label(__('app.motto'))
                            ->helperText(__('app.motto_helper'))
                            ->maxLength(300)
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
                    ]),
                Section::make(__('app.section_rsvp'))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        DatePicker::make('rsvp_deadline')
                            ->label(__('app.rsvp_deadline'))
                            ->native(false)
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false))
                            ->helperText(fn (?WeddingEvent $record): ?string => $record?->isArchived()
                                ? __('app.rsvp_deadline_locked')
                                : null),
                        Toggle::make('accommodation_enabled')
                            ->label(__('app.accommodation_enabled'))
                            ->helperText(__('app.accommodation_enabled_helper'))
                            ->default(false)
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
                        Select::make('invitation_locale')
                            ->label(__('app.invitation_locale'))
                            ->helperText(__('app.invitation_locale_helper'))
                            ->options(Locale::options())
                            ->required()
                            ->native(false)
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
                        Textarea::make('send_message')
                            ->label(__('app.guest_message'))
                            ->helperText(__('app.guest_message_helper'))
                            ->placeholder(__('app.guest_message_placeholder'))
                            ->rows(4)
                            ->columnSpanFull()
                            ->disabled(fn (?WeddingEvent $record): bool => $record?->isArchived() ?? false)
                            ->dehydrated(fn (?WeddingEvent $record): bool => ! ($record?->isArchived() ?? false)),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\DiscountEmailTemplates\Schemas;

use App\Support\Locale;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class DiscountEmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('discounts.section_template'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('discounts.field_name'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Toggle::make('is_active')
                            ->label(__('discounts.field_is_active'))
                            ->default(true),
                    ]),
                Section::make(__('discounts.section_template_locales'))
                    ->description(__('discounts.helper_placeholders'))
                    ->schema([
                        Tabs::make('locales')
                            ->tabs(
                                collect(Locale::supported())
                                    ->map(function (string $locale): Tab {
                                        $label = Locale::options()[$locale] ?? strtoupper($locale);

                                        return Tab::make($label)
                                            ->schema([
                                                TextInput::make("subjects.{$locale}")
                                                    ->label(__('discounts.field_subject_locale', ['locale' => $label]))
                                                    ->required()
                                                    ->maxLength(255),
                                                Textarea::make("bodies.{$locale}")
                                                    ->label(__('discounts.field_body_locale', ['locale' => $label]))
                                                    ->required()
                                                    ->rows(5),
                                            ]);
                                    })
                                    ->all()
                            ),
                    ]),
            ]);
    }
}

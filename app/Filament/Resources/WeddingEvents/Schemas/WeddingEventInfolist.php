<?php

namespace App\Filament\Resources\WeddingEvents\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WeddingEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('groom_name'),
                        TextEntry::make('bride_name'),
                        TextEntry::make('slug'),
                        TextEntry::make('wedding_date')
                            ->dateTime(),
                        TextEntry::make('theme'),
                        TextEntry::make('link_mode'),
                        TextEntry::make('public_url')
                            ->label('Public link')
                            ->copyable(),
                        IconEntry::make('is_active')
                            ->boolean(),
                    ]),
            ]);
    }
}

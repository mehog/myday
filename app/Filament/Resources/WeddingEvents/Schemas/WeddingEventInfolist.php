<?php

namespace App\Filament\Resources\WeddingEvents\Schemas;

use App\LinkType;
use App\Models\WeddingEvent;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

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
                        IconEntry::make('is_demo')
                            ->label('Demo invitation')
                            ->boolean(),
                    ]),
                Section::make('Link Visits')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('link_visits_total')
                            ->label('Total views')
                            ->getStateUsing(fn (WeddingEvent $record): int => $record->linkVisits()->count()),
                        TextEntry::make('link_visits_public')
                            ->label('Public link views')
                            ->getStateUsing(fn (WeddingEvent $record): int => $record->linkVisits()->where('link_type', LinkType::Public)->count()),
                        TextEntry::make('link_visits_personal')
                            ->label('Personal link views')
                            ->getStateUsing(fn (WeddingEvent $record): int => $record->linkVisits()->where('link_type', LinkType::Personal)->count()),
                        TextEntry::make('link_visits_last_opened')
                            ->label('Last opened')
                            ->getStateUsing(function (WeddingEvent $record): ?string {
                                $lastVisitedAt = $record->linkVisits()->max('visited_at');

                                return $lastVisitedAt
                                    ? Carbon::parse($lastVisitedAt)->diffForHumans()
                                    : null;
                            })
                            ->placeholder('—'),
                    ]),
            ]);
    }
}

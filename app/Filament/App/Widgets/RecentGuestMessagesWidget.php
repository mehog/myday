<?php

namespace App\Filament\App\Widgets;

use App\Models\Guest;
use App\RsvpStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentGuestMessagesWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $weddingEventId = auth()->user()?->weddingEvent?->id;

        return $table
            ->heading(__('app.recent_rsvp_notes_heading'))
            ->query(fn (): Builder => Guest::query()
                ->when($weddingEventId, fn (Builder $query) => $query->where('wedding_event_id', $weddingEventId))
                ->when(! $weddingEventId, fn (Builder $query) => $query->whereRaw('1 = 0'))
                ->whereNotNull('rsvp_note')
                ->whereNotNull('rsvp_responded_at')
                ->orderByDesc('rsvp_responded_at')
                ->limit(5))
            ->defaultSort('rsvp_responded_at', 'desc')
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label(__('guests.field_name')),
                TextColumn::make('rsvp_status')
                    ->label(__('guests.field_rsvp_status'))
                    ->badge()
                    ->formatStateUsing(fn (?RsvpStatus $state): ?string => $state?->label())
                    ->color(fn (?RsvpStatus $state): string => match ($state) {
                        RsvpStatus::Yes => 'success',
                        RsvpStatus::No => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('rsvp_note')
                    ->label(__('guests.field_rsvp_note'))
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('rsvp_responded_at')
                    ->label(__('guests.field_rsvp_responded_at'))
                    ->since()
                    ->placeholder('—'),
            ])
            ->emptyStateHeading(__('app.recent_rsvp_notes_empty'))
            ->emptyStateDescription(__('app.recent_rsvp_notes_empty_desc'));
    }
}

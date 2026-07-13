<?php

namespace App\Filament\Widgets;

use App\Models\Enquiry;
use App\Support\AdminDashboardMetrics;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentEnquiriesWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent enquiries')
            ->query(fn (): Builder => AdminDashboardMetrics::recentEnquiriesQuery()->limit(8))
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Contact'),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('couple')
                    ->label('Couple')
                    ->state(fn (Enquiry $record): string => "{$record->groom_name} & {$record->bride_name}"),
                TextColumn::make('wedding_date')
                    ->label('Wedding date')
                    ->date()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->url(fn (Enquiry $record): string => "mailto:{$record->email}"),
            ])
            ->emptyStateHeading('No enquiries yet')
            ->emptyStateDescription('Contact form submissions will appear here.');
    }
}

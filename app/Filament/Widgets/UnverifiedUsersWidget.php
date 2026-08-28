<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\AdminDashboardMetrics;
use App\Support\MediaDisk;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UnverifiedUsersWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Unverified users')
            ->query(fn (): Builder => AdminDashboardMetrics::unverifiedUsersQuery()->limit(8))
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->columns([
                ImageColumn::make('weddingEvent.hero_image')
                    ->label('Cover')
                    ->disk(MediaDisk::name())
                    ->height(56)
                    ->width(56)
                    ->square()
                    ->placeholder('—'),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('weddingEvent.motto')
                    ->label('Motto')
                    ->limit(50)
                    ->tooltip(fn (User $record): ?string => $record->weddingEvent?->motto)
                    ->placeholder('—')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Signed up')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('All users verified')
            ->emptyStateDescription('No couples are waiting on email confirmation.');
    }
}

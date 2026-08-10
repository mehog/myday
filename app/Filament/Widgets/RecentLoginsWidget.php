<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\AdminDashboardMetrics;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog;

class RecentLoginsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent logins')
            ->query(fn (): Builder => AdminDashboardMetrics::recentSuccessfulLoginsQuery())
            ->paginated(false)
            ->columns([
                TextColumn::make('authenticatable.name')
                    ->label('User')
                    ->placeholder('—'),
                TextColumn::make('authenticatable.email')
                    ->label('Email')
                    ->placeholder('—'),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('—'),
                TextColumn::make('device_name')
                    ->label('Device')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('login_at')
                    ->label('Logged in')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (AuthenticationLog $record): bool => $record->authenticatable instanceof User)
                    ->url(fn (AuthenticationLog $record): string => UserResource::getUrl('edit', [
                        'record' => $record->authenticatable,
                    ])),
            ])
            ->emptyStateHeading('No recent logins')
            ->emptyStateDescription('Successful sign-ins will appear here.');
    }
}

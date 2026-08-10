<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AuthenticationsRelationManager extends RelationManager
{
    protected static string $relationship = 'authentications';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Logins';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('login_at', 'desc')
            ->columns([
                TextColumn::make('login_successful')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Success' : 'Failed')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('device_name')
                    ->label('Device')
                    ->placeholder('—'),
                TextColumn::make('user_agent')
                    ->label('User agent')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('login_at')
                    ->label('Logged in')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('logout_at')
                    ->label('Logged out')
                    ->dateTime()
                    ->placeholder('—'),
                TextColumn::make('last_activity_at')
                    ->label('Last activity')
                    ->since()
                    ->placeholder('—'),
                IconColumn::make('is_suspicious')
                    ->label('Suspicious')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('login_successful')
                    ->label('Status')
                    ->options([
                        '1' => 'Success',
                        '0' => 'Failed',
                    ]),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateIcon('heroicon-o-arrow-right-end-on-rectangle')
            ->emptyStateHeading('No logins yet')
            ->emptyStateDescription('Successful and failed sign-ins for this user will appear here.');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

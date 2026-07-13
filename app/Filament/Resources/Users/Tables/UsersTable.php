<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Support\AdminUserVerification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean(),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                TextColumn::make('weddingEvent.couple_names')
                    ->label('Wedding')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Verified')
                    ->placeholder('All')
                    ->trueLabel('Verified')
                    ->falseLabel('Unverified')
                    ->nullable(),
                TernaryFilter::make('is_admin')
                    ->label('Admin')
                    ->placeholder('All')
                    ->trueLabel('Admins')
                    ->falseLabel('Customers')
                    ->boolean(),
                TernaryFilter::make('has_wedding')
                    ->label('Has wedding')
                    ->placeholder('All')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('weddingEvent'),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('weddingEvent'),
                    ),
            ])
            ->recordActions([
                Action::make('verifyEmail')
                    ->label('Verify email')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Manually verify email?')
                    ->modalDescription(fn (User $record): string => "Mark {$record->email} as verified without the user clicking the link.")
                    ->visible(fn (User $record): bool => ! $record->hasVerifiedEmail())
                    ->action(function (User $record): void {
                        AdminUserVerification::verify($record);

                        Notification::make()
                            ->title('Email verified')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

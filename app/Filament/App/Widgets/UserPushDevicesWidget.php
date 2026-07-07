<?php

namespace App\Filament\App\Widgets;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use NotificationChannels\WebPush\PushSubscription;

class UserPushDevicesWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    #[On('user-push-subscribed')]
    public function refreshDevices(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->heading(__('app.push_devices_heading'))
            ->description(__('app.push_devices_description'))
            ->query(fn (): Builder => PushSubscription::query()
                ->when($user, fn (Builder $query) => $query
                    ->where('subscribable_type', $user->getMorphClass())
                    ->where('subscribable_id', $user->getKey()))
                ->when(! $user, fn (Builder $query) => $query->whereRaw('1 = 0'))
                ->orderByDesc('created_at'))
            ->paginated(false)
            ->columns([
                TextColumn::make('device_label')
                    ->label(__('app.push_devices_col_device'))
                    ->placeholder(__('app.push_devices_unknown')),
                TextColumn::make('created_at')
                    ->label(__('app.push_devices_col_registered'))
                    ->since(),
            ])
            ->headerActions([
                Action::make('addDevice')
                    ->label(__('app.push_devices_add'))
                    ->icon(Heroicon::OutlinedBell)
                    ->alpineClickHandler(<<<'JS'
                        subscribeToPushAsUser().then((result) => {
                            if (result.ok) {
                                window.dispatchEvent(new CustomEvent('user-push-subscribed'));
                            }
                        });
                    JS),
            ])
            ->recordActions([
                Action::make('remove')
                    ->label(__('app.push_devices_remove'))
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('app.push_devices_remove_confirm_heading'))
                    ->modalDescription(__('app.push_devices_remove_confirm_body'))
                    ->action(function (PushSubscription $record): void {
                        $user = auth()->user();

                        if ($user === null || ! $user->ownsPushSubscription($record)) {
                            return;
                        }

                        $record->delete();

                        Notification::make()
                            ->title(__('app.push_devices_removed'))
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading(__('app.push_devices_empty_heading'))
            ->emptyStateDescription(__('app.push_devices_empty_desc'));
    }
}

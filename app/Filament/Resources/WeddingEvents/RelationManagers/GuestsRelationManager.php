<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Filament\Imports\GuestImporter;
use App\InvitePlatform;
use App\Models\Guest;
use App\RsvpStatus;
use App\Support\Clipboard;
use App\Support\MessengerLinks;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GuestsRelationManager extends RelationManager
{
    protected static string $relationship = 'guests';

    protected static ?string $title = 'Gosti';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Ime')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Telefon')
                    ->tel()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('weddingEvent'))
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Ime')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('rsvp_status')
                    ->label('RSVP status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?RsvpStatus $state) => $state?->label() ?? $this->trans('rsvp_pending')),
                TextColumn::make('rsvp_responded_at')
                    ->label('Datum odgovora')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('invite_platform')
                    ->label($this->trans('sent_via'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?InvitePlatform $state) => $state?->label())
                    ->placeholder('—'),
                TextColumn::make('invite_sent_at')
                    ->label($this->trans('invite_sent'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('personal_url')
                    ->label($this->trans('personal_link'))
                    ->formatStateUsing(fn () => 'Link')
                    ->copyable()
                    ->copyableState(fn (Guest $record): string => $record->personal_url)
                    ->copyMessage($this->trans('link_copied')),
            ])
            ->headerActions([
                CreateAction::make(),
                Action::make('importCsv')
                    ->label($this->trans('import_csv'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('file')
                            ->label($this->trans('csv_file'))
                            ->disk('local')
                            ->directory('temp/csv-imports')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $contents = \Illuminate\Support\Facades\Storage::disk('local')->get($data['file']);
                        $count = GuestImporter::importFromContents($this->getOwnerRecord(), $contents);

                        \Illuminate\Support\Facades\Storage::disk('local')->delete($data['file']);

                        Notification::make()
                            ->title($this->trans('imported_count', ['count' => $count]))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('copyPersonalLink')
                    ->label($this->trans('copy_link'))
                    ->icon('heroicon-o-clipboard')
                    ->color('gray')
                    ->alpineClickHandler(fn (Guest $record): string => Clipboard::alpineCopy(
                        $record->personal_url,
                        $this->trans('link_copied'),
                    )),
                Action::make('sendInvite')
                    ->label($this->trans('send_invite'))
                    ->modalHeading($this->trans('send_invite'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (Action $action) => $action->label($this->trans('close')))
                    ->fillForm(fn (Guest $record): array => [
                        'message' => $record->weddingEvent->composeSendMessage($record),
                    ])
                    ->form([
                        Textarea::make('message')
                            ->label($this->trans('message'))
                            ->rows(5)
                            ->readOnly(),
                    ])
                    ->extraModalFooterActions(fn (Action $action): array => [
                        $this->messagingAction($action, InvitePlatform::WhatsApp),
                        $this->messagingAction($action, InvitePlatform::Viber),
                        $this->messagingAction($action, InvitePlatform::Telegram),
                    ]),
                Action::make('markSent')
                    ->label($this->trans('mark_sent'))
                    ->modalHeading($this->trans('mark_sent'))
                    ->icon('heroicon-o-check-circle')
                    ->color('gray')
                    ->form([
                        Select::make('invite_platform')
                            ->label($this->trans('platform'))
                            ->options(collect(InvitePlatform::cases())->mapWithKeys(fn (InvitePlatform $platform) => [$platform->value => $platform->label()]))
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (array $data, Guest $record): void {
                        $record->update([
                            'invite_sent_at' => now(),
                            'invite_platform' => $data['invite_platform'],
                        ]);

                        Notification::make()
                            ->title($this->trans('guest_marked_sent'))
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function messagingAction(Action $parentAction, InvitePlatform $platform): Action
    {
        return Action::make($platform->value)
            ->label($platform->label())
            ->action(function (Guest $record) use ($platform): void {
                $record->update([
                    'invite_sent_at' => now(),
                    'invite_platform' => $platform,
                ]);
            })
            ->alpineClickHandler(function (Guest $record) use ($platform): string {
                $message = $record->weddingEvent->composeSendMessage($record);

                $url = match ($platform) {
                    InvitePlatform::WhatsApp => MessengerLinks::whatsApp($record, $message),
                    InvitePlatform::Viber => MessengerLinks::viber($message),
                    InvitePlatform::Telegram => MessengerLinks::telegram($record, $message),
                    InvitePlatform::Manual => $record->personal_url,
                };

                return MessengerLinks::openInNewTab($url);
            })
            ->cancelParentActions();
    }

    protected function trans(string $key, array $replace = []): string
    {
        return __("guests.{$key}", $replace, 'bs');
    }
}

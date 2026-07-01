<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Filament\Imports\GuestImporter;
use App\InvitePlatform;
use App\Models\Guest;
use App\RsvpStatus;
use App\Support\Clipboard;
use App\Support\MessengerLinks;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class GuestsRelationManager extends RelationManager
{
    protected static string $relationship = 'guests';

    protected static ?string $title = null;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('guests.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label($this->trans('field_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label($this->trans('field_email'))
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label($this->trans('field_phone'))
                    ->tel()
                    ->maxLength(255),
                Toggle::make('plus_one_allowed')
                    ->label($this->trans('field_plus_one_allowed'))
                    ->helperText($this->trans('field_plus_one_allowed_helper'))
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with('weddingEvent')
                ->withMax('linkVisits as last_visited_at', 'visited_at'))
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->getStateUsing(fn (): null => null)
                    ->defaultImageUrl(fn (Guest $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&background=f43f5e&color=fff&size=128')
                    ->circular()
                    ->width(40)
                    ->height(40),
                TextColumn::make('name')
                    ->label($this->trans('field_name'))
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('email')
                    ->label($this->trans('field_email'))
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rsvp_status')
                    ->label($this->trans('field_rsvp_status'))
                    ->badge()
                    ->sortable()
                    ->color(fn (?RsvpStatus $state): string => match ($state) {
                        RsvpStatus::Yes => 'success',
                        RsvpStatus::No => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(function (?RsvpStatus $state, Guest $record): string {
                        $label = $state?->label() ?? $this->trans('rsvp_pending');

                        if ($record->rsvp_manual_override) {
                            return $label.' ('.$this->trans('rsvp_manual_flag').')';
                        }

                        return $label;
                    }),
                TextColumn::make('rsvp_responded_at')
                    ->label($this->trans('field_rsvp_responded_at'))
                    ->since()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('plus_one_name')
                    ->label($this->trans('field_plus_one_name'))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('rsvp_note')
                    ->label($this->trans('field_rsvp_note'))
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('invite_platform')
                    ->label($this->trans('sent_via'))
                    ->sortable()
                    ->icon(fn (?InvitePlatform $state): ?Heroicon => match ($state) {
                        InvitePlatform::WhatsApp => Heroicon::OutlinedChatBubbleLeftRight,
                        InvitePlatform::Viber => Heroicon::OutlinedPhone,
                        InvitePlatform::Telegram => Heroicon::OutlinedPaperAirplane,
                        InvitePlatform::FacebookMessenger => Heroicon::OutlinedChatBubbleOvalLeft,
                        InvitePlatform::Manual => Heroicon::OutlinedHandRaised,
                        default => null,
                    })
                    ->tooltip(fn (?InvitePlatform $state): ?string => $state?->label())
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('invite_sent_at')
                    ->label($this->trans('invite_sent'))
                    ->since()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('last_visited_at')
                    ->label($this->trans('last_opened'))
                    ->since()
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->emptyStateIcon(Heroicon::OutlinedUserGroup)
            ->emptyStateHeading($this->trans('empty_heading'))
            ->emptyStateDescription($this->trans('empty_description'))
            ->emptyStateActions([
                CreateAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
                Action::make('importCsv')
                    ->label($this->trans('import_csv'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('file')
                            ->label($this->trans('csv_file'))
                            ->disk('local')
                            ->directory('temp/csv-imports')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $contents = Storage::disk('local')->get($data['file']);
                        $count = GuestImporter::importFromContents($this->getOwnerRecord(), $contents);

                        Storage::disk('local')->delete($data['file']);

                        Notification::make()
                            ->title($this->trans('imported_count', ['count' => $count]))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('sendInvite')
                    ->label($this->trans('send_invite'))
                    ->modalHeading($this->trans('send_invite'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->button()
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
                        $this->messagingAction($action, InvitePlatform::FacebookMessenger),
                    ]),
                ActionGroup::make([
                    Action::make('copyPersonalLink')
                        ->label($this->trans('copy_link'))
                        ->icon('heroicon-o-clipboard')
                        ->color('gray')
                        ->alpineClickHandler(fn (Guest $record): string => Clipboard::alpineCopy(
                            $record->personal_url,
                            $this->trans('link_copied'),
                        )),
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
                    Action::make('markRsvp')
                        ->label($this->trans('mark_rsvp'))
                        ->modalHeading($this->trans('mark_rsvp'))
                        ->modalDescription($this->trans('mark_rsvp_description'))
                        ->icon('heroicon-o-pencil-square')
                        ->color('gray')
                        ->fillForm(fn (Guest $record): array => [
                            'rsvp_status' => $record->rsvp_status?->value,
                        ])
                        ->form([
                            Select::make('rsvp_status')
                                ->label($this->trans('field_rsvp_status'))
                                ->options(collect(RsvpStatus::cases())->mapWithKeys(
                                    fn (RsvpStatus $status) => [$status->value => $status->label()]
                                ))
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (array $data, Guest $record): void {
                            $rsvpStatus = RsvpStatus::from($data['rsvp_status']);

                            $record->update([
                                'rsvp_status' => $rsvpStatus,
                                'rsvp_responded_at' => now(),
                                'rsvp_manual_override' => true,
                                'plus_one_name' => $rsvpStatus === RsvpStatus::Yes ? $record->plus_one_name : null,
                            ]);

                            Notification::make()
                                ->title($this->trans('rsvp_marked'))
                                ->success()
                                ->send();
                        }),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->label($this->trans('more_actions'))
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('gray')
                    ->button(),
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
                    InvitePlatform::FacebookMessenger => MessengerLinks::facebookMessenger($record, $message),
                    InvitePlatform::Manual => $record->personal_url,
                };

                return MessengerLinks::openInNewTab($url);
            })
            ->livewireClickHandlerEnabled(true)
            ->cancelParentActions();
    }

    protected function trans(string $key, array $replace = []): string
    {
        return __("guests.{$key}", $replace);
    }
}

<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers;

use App\Filament\Imports\GuestImporter;
use App\GuestLabel;
use App\InvitePlatform;
use App\Models\Guest;
use App\Models\GuestChild;
use App\Models\WeddingMenuOption;
use App\PlanFeature;
use App\RsvpStatus;
use App\Services\SyncGuestChildren;
use App\Support\Clipboard;
use App\Support\Locale;
use App\Support\MessengerLinks;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class GuestsRelationManager extends RelationManager
{
    protected static string $relationship = 'guests';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
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
                    ->maxLength(255)
                    ->hidden(),
                TextInput::make('phone')
                    ->label($this->trans('field_phone'))
                    ->tel()
                    ->maxLength(255)
                    ->hidden(),
                Select::make('invitation_locale')
                    ->label($this->trans('field_invitation_locale'))
                    ->helperText($this->trans('field_invitation_locale_helper'))
                    ->options(Locale::options())
                    ->placeholder($this->trans('field_invitation_locale_default'))
                    ->nullable()
                    ->native(false)
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null),
                Toggle::make('plus_one_allowed')
                    ->label($this->trans('field_plus_one_allowed'))
                    ->helperText($this->trans('field_plus_one_allowed_helper'))
                    ->default(false),
                CheckboxList::make('labels')
                    ->label($this->trans('field_labels'))
                    ->helperText($this->trans('field_labels_helper'))
                    ->options(GuestLabel::options())
                    ->columns(2)
                    ->bulkToggleable()
                    ->afterStateHydrated(function (CheckboxList $component, mixed $state): void {
                        $component->state(
                            Collection::wrap($state)
                                ->map(fn (mixed $label): ?string => $label instanceof GuestLabel ? $label->value : (is_string($label) ? $label : null))
                                ->filter()
                                ->values()
                                ->all()
                        );
                    })
                    ->dehydrateStateUsing(fn (?array $state): ?array => filled($state) ? array_values($state) : null),
                Placeholder::make('rsvp_note')
                    ->label($this->trans('field_rsvp_note'))
                    ->prose()
                    ->placeholder('—')
                    ->hiddenOn('create'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['weddingEvent', 'children.menuOption', 'menuOption', 'plusOneMenuOption'])
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
                    ->hidden(),
                TextColumn::make('labels')
                    ->label($this->trans('field_labels'))
                    ->badge()
                    ->getStateUsing(fn (Guest $record): array => $record->labels
                        ? $record->labels->map(fn (GuestLabel $label): string => $label->label())->all()
                        : [])
                    ->placeholder($this->trans('labels_none'))
                    ->toggleable(),
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
                TextColumn::make('children_names')
                    ->label($this->trans('field_children'))
                    ->getStateUsing(fn (Guest $record): string => $record->children
                        ->map(fn (GuestChild $child): string => $child->displayName())
                        ->implode(', '))
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('menus')
                    ->label($this->trans('field_menus'))
                    ->getStateUsing(fn (Guest $record): string => $this->formatGuestMenus($record))
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('accommodation_count')
                    ->label($this->trans('field_accommodation'))
                    ->formatStateUsing(fn (?int $state): string => ($state ?? 0) > 0
                        ? $this->trans('accommodation_count_value', ['count' => $state])
                        : $this->trans('accommodation_none'))
                    ->placeholder($this->trans('accommodation_none'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('rsvp_note')
                    ->label($this->trans('field_rsvp_note'))
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->sortable()
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
            ->filters([
                SelectFilter::make('labels')
                    ->label($this->trans('filter_labels'))
                    ->multiple()
                    ->options([
                        ...GuestLabel::options(),
                        'none' => $this->trans('labels_none'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $values = array_values(array_filter($data['values'] ?? []));

                        if ($values === []) {
                            return $query;
                        }

                        return $query->where(function (Builder $labelsQuery) use ($values): void {
                            foreach ($values as $value) {
                                if ($value === 'none') {
                                    $labelsQuery->orWhere(function (Builder $unlabeled): void {
                                        $unlabeled
                                            ->whereNull('labels')
                                            ->orWhere('labels', '[]')
                                            ->orWhere('labels', 'null');
                                    });

                                    continue;
                                }

                                $labelsQuery->orWhereJsonContains('labels', $value);
                            }
                        });
                    }),
                TrashedFilter::make(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedUserGroup)
            ->emptyStateHeading($this->trans('empty_heading'))
            ->emptyStateDescription($this->trans('empty_description'))
            ->emptyStateActions([
                CreateAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked() && $this->canAddMoreGuests())
                    ->before(fn (CreateAction $action) => $this->guardGuestCapacity($action)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (): bool => ! $this->coupleSetupLocked() && $this->canAddMoreGuests())
                    ->before(fn (CreateAction $action) => $this->guardGuestCapacity($action)),
                Action::make('downloadPlaceCards')
                    ->label($this->trans('place_cards_download'))
                    ->modalHeading($this->trans('place_cards_download'))
                    ->modalDescription($this->trans('place_cards_modal_description'))
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->before(function (Action $action): void {
                        if (! $this->getOwnerRecord()
                            ->guests()
                            ->where('rsvp_status', RsvpStatus::Yes)
                            ->exists()) {
                            Notification::make()
                                ->title($this->trans('place_cards_empty'))
                                ->warning()
                                ->send();

                            $action->halt();
                        }
                    })
                    ->fillForm(fn (): array => $this->getOwnerRecord()->theme->placeCardColors())
                    ->form([
                        Placeholder::make('place_cards_preview')
                            ->hiddenLabel()
                            ->content(fn (): HtmlString => new HtmlString(
                                view('components.place-card-preview')->render()
                            )),
                        Section::make()
                            ->columns(3)
                            ->schema([
                                ColorPicker::make('bg')
                                    ->label($this->trans('place_cards_color_bg'))
                                    ->required(),
                                ColorPicker::make('text')
                                    ->label($this->trans('place_cards_color_text'))
                                    ->required(),
                                ColorPicker::make('accent')
                                    ->label($this->trans('place_cards_color_accent'))
                                    ->required(),
                            ]),
                        Placeholder::make('place_cards_print_hint')
                            ->hiddenLabel()
                            ->content($this->trans('place_cards_print_hint')),
                    ])
                    ->action(function (array $data, Action $action): void {
                        $wedding = $this->getOwnerRecord();

                        if (
                            filament()->getCurrentPanel()?->getId() === 'app'
                            && ! $wedding->hasFeature(PlanFeature::QrPhotoAlbum)
                        ) {
                            $this->dispatch('open-upgrade-modal');
                            $action->halt();

                            return;
                        }

                        $url = route('guests.place-cards.download', [
                            'bg' => $data['bg'],
                            'text' => $data['text'],
                            'accent' => $data['accent'],
                        ]);

                        $this->js('window.open('.json_encode($url).", '_blank')");
                        $action->halt();
                    }),
                Action::make('importCsv')
                    ->label($this->trans('import_csv'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->visible(false)
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

                        try {
                            $count = GuestImporter::importFromContents($this->getOwnerRecord(), $contents);
                        } catch (\Throwable $e) {
                            Storage::disk('local')->delete($data['file']);

                            Notification::make()
                                ->title($e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

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
                    ->visible(fn (): bool => ! $this->coupleSetupLocked())
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
                        ->visible(fn (): bool => ! $this->coupleSetupLocked())
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
                        ->visible(fn (): bool => ! $this->coupleSetupLocked())
                        ->fillForm(fn (Guest $record): array => [
                            'rsvp_status' => $record->rsvp_status?->value,
                            'plus_one_name' => $record->plus_one_name,
                            'menu_option_id' => $record->menu_option_id,
                            'plus_one_menu_option_id' => $record->plus_one_menu_option_id,
                            'accommodation_count' => $record->accommodation_count,
                        ])
                        ->form(fn (Guest $record): array => [
                            Select::make('rsvp_status')
                                ->label($this->trans('field_rsvp_status'))
                                ->options(collect(RsvpStatus::cases())->mapWithKeys(
                                    fn (RsvpStatus $status) => [$status->value => $status->label()]
                                ))
                                ->required()
                                ->native(false)
                                ->live(),
                            TextInput::make('plus_one_name')
                                ->label($this->trans('field_plus_one_name'))
                                ->maxLength(255)
                                ->visible(fn (callable $get): bool => $record->plus_one_allowed && $get('rsvp_status') === RsvpStatus::Yes->value)
                                ->live(),
                            Select::make('menu_option_id')
                                ->label($this->trans('field_menu'))
                                ->options(fn (): array => $this->menuOptionOptions())
                                ->native(false)
                                ->visible(fn (callable $get): bool => $get('rsvp_status') === RsvpStatus::Yes->value
                                    && $this->menuOptionOptions() !== []),
                            Select::make('plus_one_menu_option_id')
                                ->label($this->trans('field_plus_one_menu'))
                                ->options(fn (): array => $this->menuOptionOptions())
                                ->native(false)
                                ->visible(fn (callable $get): bool => $record->plus_one_allowed
                                    && $get('rsvp_status') === RsvpStatus::Yes->value
                                    && filled($get('plus_one_name'))
                                    && $this->menuOptionOptions() !== []),
                            TextInput::make('accommodation_count')
                                ->label($this->trans('field_accommodation_count'))
                                ->numeric()
                                ->minValue(0)
                                ->visible(fn (callable $get): bool => $this->getOwnerRecord()->accommodation_enabled
                                    && $get('rsvp_status') === RsvpStatus::Yes->value),
                        ])
                        ->action(function (array $data, Guest $record): void {
                            $rsvpStatus = RsvpStatus::from($data['rsvp_status']);

                            $plusOneName = null;
                            $menuOptionId = null;
                            $plusOneMenuOptionId = null;
                            $accommodationCount = null;

                            if ($rsvpStatus === RsvpStatus::Yes) {
                                if ($record->plus_one_allowed) {
                                    $plusOneName = filled($data['plus_one_name'] ?? null)
                                        ? trim($data['plus_one_name'])
                                        : null;
                                }

                                $menuOptionId = filled($data['menu_option_id'] ?? null)
                                    ? (int) $data['menu_option_id']
                                    : null;

                                if ($plusOneName !== null) {
                                    $plusOneMenuOptionId = filled($data['plus_one_menu_option_id'] ?? null)
                                        ? (int) $data['plus_one_menu_option_id']
                                        : null;
                                }

                                if ($this->getOwnerRecord()->accommodation_enabled) {
                                    $accommodationCount = max(0, (int) ($data['accommodation_count'] ?? 0));
                                    $accommodationCount = $accommodationCount > 0 ? $accommodationCount : null;
                                }
                            }

                            $record->update([
                                'rsvp_status' => $rsvpStatus,
                                'rsvp_responded_at' => now(),
                                'rsvp_manual_override' => true,
                                'plus_one_name' => $plusOneName,
                                'menu_option_id' => $menuOptionId,
                                'plus_one_menu_option_id' => $plusOneMenuOptionId,
                                'accommodation_count' => $accommodationCount,
                            ]);

                            Notification::make()
                                ->title($this->trans('rsvp_marked'))
                                ->success()
                                ->send();
                        }),
                    Action::make('editPlusOneSeatingName')
                        ->label($this->trans('seating_name'))
                        ->modalHeading($this->trans('seating_name'))
                        ->modalDescription($this->trans('seating_name_description'))
                        ->icon('heroicon-o-identification')
                        ->color('gray')
                        ->visible(fn (Guest $record): bool => ! $this->coupleSetupLocked() && filled($record->plus_one_name))
                        ->fillForm(fn (Guest $record): array => [
                            'plus_one_seating_name' => $record->plus_one_seating_name,
                        ])
                        ->form(fn (Guest $record): array => [
                            Placeholder::make('plus_one_name')
                                ->label($this->trans('field_plus_one_name'))
                                ->content($record->plus_one_name ?? '—'),
                            TextInput::make('plus_one_seating_name')
                                ->label($this->trans('field_plus_one_seating_name'))
                                ->helperText($this->trans('field_plus_one_seating_name_helper'))
                                ->maxLength(255),
                        ])
                        ->action(function (array $data, Guest $record): void {
                            $record->update([
                                'plus_one_seating_name' => filled($data['plus_one_seating_name'] ?? null)
                                    ? trim($data['plus_one_seating_name'])
                                    : null,
                            ]);

                            Notification::make()
                                ->title($this->trans('seating_name_saved'))
                                ->success()
                                ->send();
                        }),
                    Action::make('editChildren')
                        ->label($this->trans('children'))
                        ->modalHeading($this->trans('children'))
                        ->modalDescription($this->trans('children_description'))
                        ->icon('heroicon-o-user-group')
                        ->color('gray')
                        ->visible(fn (): bool => ! $this->coupleSetupLocked())
                        ->fillForm(fn (Guest $record): array => [
                            'children' => $record->children
                                ->map(fn (GuestChild $child): array => [
                                    'id' => $child->id,
                                    'name' => $child->name,
                                    'seating_name' => $child->seating_name,
                                    'menu_option_id' => $child->menu_option_id,
                                ])
                                ->all(),
                        ])
                        ->form([
                            Repeater::make('children')
                                ->label($this->trans('field_children'))
                                ->helperText($this->trans('children_helper'))
                                ->schema([
                                    TextInput::make('id')
                                        ->hidden()
                                        ->dehydrated(),
                                    TextInput::make('name')
                                        ->label($this->trans('field_child_name'))
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('seating_name')
                                        ->label($this->trans('field_child_seating_name'))
                                        ->helperText($this->trans('field_child_seating_name_helper'))
                                        ->maxLength(255),
                                    Select::make('menu_option_id')
                                        ->label($this->trans('field_child_menu'))
                                        ->options(fn (): array => $this->menuOptionOptions())
                                        ->native(false)
                                        ->visible(fn (): bool => $this->menuOptionOptions() !== []),
                                ])
                                ->defaultItems(0)
                                ->reorderable()
                                ->reorderableWithButtons()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                ->addActionLabel($this->trans('children_add'))
                                ->maxItems(GuestChild::MAX_PER_GUEST),
                        ])
                        ->action(function (array $data, Guest $record): void {
                            app(SyncGuestChildren::class)->syncFromAdmin(
                                $record,
                                $data['children'] ?? [],
                            );

                            Notification::make()
                                ->title($this->trans('children_saved'))
                                ->success()
                                ->send();
                        }),
                    EditAction::make()
                        ->visible(fn (): bool => ! $this->coupleSetupLocked()),
                    DeleteAction::make()
                        ->visible(fn (Guest $record): bool => ! $this->coupleSetupLocked() && ! $record->trashed()),
                    RestoreAction::make()
                        ->visible(fn (Guest $record): bool => ! $this->coupleSetupLocked() && $record->trashed()),
                    ForceDeleteAction::make()
                        ->visible(fn (Guest $record): bool => ! $this->coupleSetupLocked() && $record->trashed()),
                ])
                    ->label($this->trans('more_actions'))
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => ! $this->coupleSetupLocked()),
                    RestoreBulkAction::make()
                        ->visible(fn (): bool => ! $this->coupleSetupLocked()),
                    ForceDeleteBulkAction::make()
                        ->visible(fn (): bool => ! $this->coupleSetupLocked()),
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

    /**
     * @return array<int, string>
     */
    protected function menuOptionOptions(): array
    {
        return $this->getOwnerRecord()
            ->menuOptions()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (WeddingMenuOption $option): array => [
                $option->id => $option->displayLabel(),
            ])
            ->all();
    }

    protected function formatGuestMenus(Guest $record): string
    {
        $parts = [];

        if ($record->menuOption) {
            $parts[] = $record->name.': '.$record->menuOption->displayLabel();
        }

        if ($record->plusOneMenuOption && filled($record->plus_one_name)) {
            $parts[] = $record->plus_one_name.': '.$record->plusOneMenuOption->displayLabel();
        }

        foreach ($record->children as $child) {
            if (! $child->menuOption) {
                continue;
            }

            $parts[] = $child->displayName().': '.$child->menuOption->displayLabel();
        }

        return implode(' · ', $parts);
    }

    protected function coupleSetupLocked(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'app'
            && $this->getOwnerRecord()->isArchived();
    }

    protected function canAddMoreGuests(): bool
    {
        return $this->getOwnerRecord()->canAddGuests();
    }

    protected function guardGuestCapacity(CreateAction $action): void
    {
        $wedding = $this->getOwnerRecord();

        if ($wedding->canAddGuests()) {
            return;
        }

        Notification::make()
            ->title(__('pricing.guest_limit_reached', [
                'count' => $wedding->activeGuestCount(),
                'limit' => $wedding->guest_limit ?? 0,
            ]))
            ->danger()
            ->send();

        $action->halt();
    }

    protected function trans(string $key, array $replace = []): string
    {
        return __("guests.{$key}", $replace);
    }
}

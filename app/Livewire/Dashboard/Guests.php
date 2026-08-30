<?php

namespace App\Livewire\Dashboard;

use App\GuestLabel;
use App\InvitePlatform;
use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\Guest;
use App\Models\GuestChild;
use App\Models\WeddingEvent;
use App\Models\WeddingMenuOption;
use App\PlanFeature;
use App\RsvpStatus;
use App\Services\SyncGuestChildren;
use App\Support\Locale;
use App\Support\MessengerLinks;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Guests extends Component
{
    use RendersDashboard;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sort = 'name';

    #[Url]
    public string $direction = 'asc';

    #[Url]
    public string $filterRsvp = '';

    /** @var list<string> */
    public array $filterLabels = [];

    public string $modal = '';

    public ?int $activeGuestId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $invitation_locale = '';

    public bool $plus_one_allowed = false;

    /** @var list<string> */
    public array $labels = [];

    public string $inviteMessage = '';

    public string $invite_platform = '';

    public string $rsvp_status = '';

    public string $plus_one_name = '';

    public string $plus_one_seating_name = '';

    public ?int $menu_option_id = null;

    public ?int $plus_one_menu_option_id = null;

    public string $accommodation_count = '';

    /** @var list<array{id: ?int, name: string, seating_name: string, menu_option_id: ?int}> */
    public array $children = [];

    public string $placeCardBg = '';

    public string $placeCardText = '';

    public string $placeCardAccent = '';

    public ?string $flashMessage = null;

    public ?string $flashError = null;

    public function render()
    {
        $wedding = $this->wedding();

        return $this->dashboardView('livewire.dashboard.guests', [
            'wedding' => $wedding,
            'guests' => $this->getGuests(),
            'locked' => $this->isLocked(),
            'canAdd' => $wedding?->canAddGuests() ?? false,
            'activeGuest' => $this->activeGuest(),
            'menuOptions' => $this->menuOptionOptions(),
            'labelOptions' => GuestLabel::options(),
            'localeOptions' => Locale::options(),
            'platformOptions' => collect(InvitePlatform::cases())
                ->mapWithKeys(fn (InvitePlatform $p) => [$p->value => $p->label()])
                ->all(),
            'rsvpOptions' => collect(RsvpStatus::cases())
                ->mapWithKeys(fn (RsvpStatus $s) => [$s->value => $s->label()])
                ->all(),
        ], __('dashboard.guests_title'), [
            ['label' => __('dashboard.nav.guests'), 'url' => null],
        ]);
    }

    protected function wedding(): ?WeddingEvent
    {
        return auth()->user()?->weddingEvent;
    }

    public function isLocked(): bool
    {
        $wedding = $this->wedding();

        return $wedding instanceof WeddingEvent && $wedding->isCoupleMutationLocked();
    }

    protected function ensureWritable(WeddingEvent $wedding): void
    {
        abort_if($wedding->isCoupleMutationLocked(), 403);
    }

    protected function activeGuest(): ?Guest
    {
        if ($this->activeGuestId === null) {
            return null;
        }

        $wedding = $this->wedding();
        if (! $wedding) {
            return null;
        }

        return $wedding->guests()->withTrashed()->whereKey($this->activeGuestId)->first();
    }

    /**
     * @return Collection<int, Guest>
     */
    public function getGuests(): Collection
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return collect();
        }

        $query = $wedding->guests()
            ->with(['children.menuOption', 'menuOption', 'plusOneMenuOption'])
            ->withMax('linkVisits as last_visited_at', 'visited_at');

        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('plus_one_name', 'like', '%'.$search.'%');
            });
        }

        if ($this->filterRsvp === 'pending') {
            $query->whereNull('rsvp_status');
        } elseif (in_array($this->filterRsvp, ['yes', 'no'], true)) {
            $query->where('rsvp_status', $this->filterRsvp);
        }

        if ($this->filterLabels !== []) {
            $query->where(function (Builder $builder): void {
                foreach ($this->filterLabels as $label) {
                    if ($label === '__none') {
                        $builder->orWhereNull('labels')->orWhere('labels', '[]');

                        continue;
                    }

                    $builder->orWhereJsonContains('labels', $label);
                }
            });
        }

        $allowedSorts = ['name', 'rsvp_status', 'rsvp_responded_at', 'invite_sent_at', 'last_visited_at'];
        $sort = in_array($this->sort, $allowedSorts, true) ? $this->sort : 'name';
        $direction = $this->direction === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sort, $direction)->orderBy('id')->get();
    }

    public function clearLabelFilter(): void
    {
        $this->filterLabels = [];
    }

    /**
     * @return array<int, string>
     */
    public function menuOptionOptions(): array
    {
        $wedding = $this->wedding();

        if (! $wedding) {
            return [];
        }

        return $wedding->menuOptions()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (WeddingMenuOption $option) => [$option->id => $option->displayLabel()])
            ->all();
    }

    public function formatGuestMenus(Guest $guest): string
    {
        $parts = [];

        if ($guest->menuOption) {
            $parts[] = $guest->name.': '.$guest->menuOption->displayLabel();
        }

        if ($guest->plusOneMenuOption && filled($guest->plus_one_name)) {
            $parts[] = $guest->plus_one_name.': '.$guest->plusOneMenuOption->displayLabel();
        }

        foreach ($guest->children as $child) {
            if ($child->menuOption) {
                $parts[] = $child->displayName().': '.$child->menuOption->displayLabel();
            }
        }

        return implode(' · ', $parts);
    }

    public function setSort(string $column): void
    {
        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = 'asc';
        }
    }

    public function openCreate(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        if (! $wedding->canAddGuests()) {
            $this->dispatch('open-upgrade-modal');

            return;
        }

        $this->resetGuestForm();
        $this->modal = 'form';
    }

    public function openGuestRowActions(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->whereKey($id)->firstOrFail();
        $this->activeGuestId = $guest->id;
        $this->modal = 'row_actions';
    }

    public function openEdit(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->whereKey($id)->firstOrFail();
        $this->activeGuestId = $guest->id;
        $this->name = $guest->name;
        $this->email = (string) ($guest->email ?? '');
        $this->phone = (string) ($guest->phone ?? '');
        $this->invitation_locale = (string) ($guest->invitation_locale ?? '');
        $this->plus_one_allowed = (bool) $guest->plus_one_allowed;
        $this->labels = $guest->labels
            ? $guest->labels->map(fn (GuestLabel $label) => $label->value)->values()->all()
            : [];
        $this->modal = 'form';
    }

    public function saveGuest(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'invitation_locale' => ['nullable', 'string', 'in:'.implode(',', array_keys(Locale::options()))],
            'plus_one_allowed' => ['boolean'],
            'labels' => ['array'],
            'labels.*' => ['string'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => filled($data['email'] ?? null) ? $data['email'] : null,
            'phone' => filled($data['phone'] ?? null) ? $data['phone'] : null,
            'invitation_locale' => filled($data['invitation_locale'] ?? null) ? $data['invitation_locale'] : null,
            'plus_one_allowed' => (bool) $data['plus_one_allowed'],
            'labels' => $data['labels'] !== [] ? $data['labels'] : null,
        ];

        if ($this->activeGuestId) {
            $wedding->guests()->whereKey($this->activeGuestId)->firstOrFail()->update($payload);
        } else {
            if (! $wedding->canAddGuests()) {
                $this->dispatch('open-upgrade-modal');

                return;
            }

            $wedding->guests()->create($payload);
        }

        $this->closeModal();
        $this->flashMessage = __('dashboard.saved');
    }

    public function openSendInvite(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->whereKey($id)->firstOrFail();
        $this->activeGuestId = $guest->id;
        $this->inviteMessage = $wedding->composeSendMessage($guest);
        $this->modal = 'send';
    }

    public function sendVia(string $platform): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->whereKey($this->activeGuestId)->firstOrFail();
        $platformEnum = InvitePlatform::from($platform);
        $message = $wedding->composeSendMessage($guest);

        $guest->update([
            'invite_sent_at' => now(),
            'invite_platform' => $platformEnum,
        ]);

        $url = match ($platformEnum) {
            InvitePlatform::WhatsApp => MessengerLinks::whatsApp($guest, $message),
            InvitePlatform::Viber => MessengerLinks::viber($message),
            InvitePlatform::Telegram => MessengerLinks::telegram($guest, $message),
            InvitePlatform::FacebookMessenger => MessengerLinks::facebookMessenger($guest, $message),
            InvitePlatform::Manual => $guest->personal_url,
        };

        $this->js('window.open('.json_encode($url).', "_blank")');
        $this->flashMessage = __('guests.guest_marked_sent');
    }

    public function openMarkSent(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->whereKey($id)->firstOrFail();
        $this->activeGuestId = $guest->id;
        $this->invite_platform = $guest->invite_platform?->value ?? InvitePlatform::Manual->value;
        $this->modal = 'mark_sent';
    }

    public function saveMarkSent(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $data = $this->validate([
            'invite_platform' => ['required', 'string'],
        ]);

        $guest = $wedding->guests()->whereKey($this->activeGuestId)->firstOrFail();
        $guest->update([
            'invite_sent_at' => now(),
            'invite_platform' => InvitePlatform::from($data['invite_platform']),
        ]);

        $this->closeModal();
        $this->flashMessage = __('guests.guest_marked_sent');
    }

    public function openMarkRsvp(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->whereKey($id)->firstOrFail();
        $this->activeGuestId = $guest->id;
        $this->rsvp_status = $guest->rsvp_status?->value ?? '';
        $this->plus_one_name = (string) ($guest->plus_one_name ?? '');
        $this->menu_option_id = $guest->menu_option_id;
        $this->plus_one_menu_option_id = $guest->plus_one_menu_option_id;
        $this->accommodation_count = $guest->accommodation_count !== null ? (string) $guest->accommodation_count : '';
        $this->modal = 'rsvp';
    }

    public function saveMarkRsvp(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $data = $this->validate([
            'rsvp_status' => ['required', 'in:yes,no'],
            'plus_one_name' => ['nullable', 'string', 'max:255'],
            'menu_option_id' => ['nullable', 'integer'],
            'plus_one_menu_option_id' => ['nullable', 'integer'],
            'accommodation_count' => ['nullable', 'integer', 'min:0'],
        ]);

        $guest = $wedding->guests()->whereKey($this->activeGuestId)->firstOrFail();
        $rsvpStatus = RsvpStatus::from($data['rsvp_status']);

        $plusOneName = null;
        $menuOptionId = null;
        $plusOneMenuOptionId = null;
        $accommodationCount = null;

        if ($rsvpStatus === RsvpStatus::Yes) {
            if ($guest->plus_one_allowed) {
                $plusOneName = filled($data['plus_one_name'] ?? null) ? trim($data['plus_one_name']) : null;
            }
            $menuOptionId = $data['menu_option_id'] ?? null;
            if (filled($plusOneName)) {
                $plusOneMenuOptionId = $data['plus_one_menu_option_id'] ?? null;
            }
            if ($wedding->accommodation_enabled) {
                $count = (int) ($data['accommodation_count'] ?? 0);
                $accommodationCount = $count > 0 ? $count : null;
            }
        }

        $guest->update([
            'rsvp_status' => $rsvpStatus,
            'rsvp_responded_at' => now(),
            'rsvp_manual_override' => true,
            'plus_one_name' => $plusOneName,
            'menu_option_id' => $menuOptionId,
            'plus_one_menu_option_id' => $plusOneMenuOptionId,
            'accommodation_count' => $accommodationCount,
        ]);

        $this->closeModal();
        $this->flashMessage = __('guests.rsvp_marked');
    }

    public function openSeatingName(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->whereKey($id)->firstOrFail();
        abort_unless(filled($guest->plus_one_name), 404);

        $this->activeGuestId = $guest->id;
        $this->plus_one_seating_name = (string) ($guest->plus_one_seating_name ?? '');
        $this->modal = 'seating';
    }

    public function saveSeatingName(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $data = $this->validate([
            'plus_one_seating_name' => ['nullable', 'string', 'max:255'],
        ]);

        $guest = $wedding->guests()->whereKey($this->activeGuestId)->firstOrFail();
        $guest->update([
            'plus_one_seating_name' => filled($data['plus_one_seating_name'] ?? null)
                ? trim($data['plus_one_seating_name'])
                : null,
        ]);

        $this->closeModal();
        $this->flashMessage = __('guests.seating_name_saved');
    }

    public function openChildren(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->with('children')->whereKey($id)->firstOrFail();
        $this->activeGuestId = $guest->id;
        $this->children = $guest->children->map(fn (GuestChild $child) => [
            'id' => $child->id,
            'name' => $child->name,
            'seating_name' => (string) ($child->seating_name ?? ''),
            'menu_option_id' => $child->menu_option_id,
        ])->values()->all();
        $this->modal = 'children';
    }

    public function addChildRow(): void
    {
        if (count($this->children) >= GuestChild::MAX_PER_GUEST) {
            return;
        }

        $this->children[] = [
            'id' => null,
            'name' => '',
            'seating_name' => '',
            'menu_option_id' => null,
        ];
    }

    public function removeChildRow(int $index): void
    {
        unset($this->children[$index]);
        $this->children = array_values($this->children);
    }

    public function saveChildren(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->whereKey($this->activeGuestId)->firstOrFail();

        app(SyncGuestChildren::class)->syncFromAdmin($guest, $this->children);

        $this->closeModal();
        $this->flashMessage = __('guests.children_saved');
    }

    public function openPlaceCards(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $hasYes = $wedding->guests()->where('rsvp_status', RsvpStatus::Yes)->exists();

        if (! $hasYes) {
            $this->flashError = __('guests.place_cards_empty');

            return;
        }

        if (! $wedding->hasFeature(PlanFeature::QrPhotoAlbum)) {
            $this->dispatch('open-upgrade-modal');

            return;
        }

        $colors = $wedding->theme->placeCardColors();
        $this->placeCardBg = $colors['bg'] ?? '#ffffff';
        $this->placeCardText = $colors['text'] ?? '#000000';
        $this->placeCardAccent = $colors['accent'] ?? '#c9a227';
        $this->modal = 'place_cards';
    }

    public function placeCardsUrl(): string
    {
        return route('guests.place-cards.download', [
            'bg' => $this->placeCardBg,
            'text' => $this->placeCardText,
            'accent' => $this->placeCardAccent,
        ]);
    }

    public function deleteGuest(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $wedding->guests()->whereKey($id)->firstOrFail()->delete();
        $this->flashMessage = __('dashboard.saved');
    }

    public function restoreGuest(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $wedding->guests()->onlyTrashed()->whereKey($id)->firstOrFail()->restore();
        $this->flashMessage = __('dashboard.saved');
    }

    public function forceDeleteGuest(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $wedding->guests()->onlyTrashed()->whereKey($id)->firstOrFail()->forceDelete();
        $this->flashMessage = __('dashboard.saved');
    }

    #[On('close-dashboard-modal')]
    public function closeModal(): void
    {
        $this->modal = '';
        $this->activeGuestId = null;
        $this->resetGuestForm();
        $this->resetValidation();
    }

    protected function resetGuestForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->invitation_locale = '';
        $this->plus_one_allowed = false;
        $this->labels = [];
        $this->inviteMessage = '';
        $this->invite_platform = '';
        $this->rsvp_status = '';
        $this->plus_one_name = '';
        $this->plus_one_seating_name = '';
        $this->menu_option_id = null;
        $this->plus_one_menu_option_id = null;
        $this->accommodation_count = '';
        $this->children = [];
    }
}

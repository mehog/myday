<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\Guest;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class Guests extends Component
{
    use RendersDashboard;

    #[Url]
    public string $search = '';

    public string $newName = '';

    public bool $newPlusOneAllowed = false;

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

    /**
     * @return Collection<int, Guest>
     */
    public function getGuests(): Collection
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return collect();
        }

        $query = $wedding->guests()->orderBy('name');

        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('plus_one_name', 'like', '%'.$search.'%');
            });
        }

        return $query->get();
    }

    public function addGuest(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        if (! $wedding->canAddGuests()) {
            $this->flashError = __('pricing.guest_limit_reached', [
                'count' => $wedding->activeGuestCount(),
                'limit' => $wedding->guest_limit ?? 0,
            ]);
            $this->dispatch('open-upgrade-modal');

            return;
        }

        $data = $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newPlusOneAllowed' => ['boolean'],
        ]);

        $wedding->guests()->create([
            'name' => $data['newName'],
            'plus_one_allowed' => $data['newPlusOneAllowed'],
        ]);

        $this->newName = '';
        $this->newPlusOneAllowed = false;
        $this->flashError = null;
        $this->flashMessage = __('dashboard.saved');
    }

    public function updateRsvp(int $guestId, string $status = ''): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->whereKey($guestId)->firstOrFail();

        $rsvp = filled($status) ? RsvpStatus::from($status) : null;

        $guest->update([
            'rsvp_status' => $rsvp,
            'rsvp_responded_at' => $rsvp !== null ? ($guest->rsvp_responded_at ?? now()) : null,
            'rsvp_manual_override' => true,
        ]);

        $this->flashMessage = __('dashboard.saved');
    }

    public function deleteGuest(int $guestId): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $guest = $wedding->guests()->whereKey($guestId)->firstOrFail();
        $guest->delete();

        $this->flashMessage = __('dashboard.saved');
    }
}

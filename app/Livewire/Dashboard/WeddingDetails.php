<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\ManagesWeddingSettings;
use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\WeddingEvent;
use App\Support\Locale;
use Illuminate\Validation\Rule;
use Livewire\Component;

class WeddingDetails extends Component
{
    use ManagesWeddingSettings;
    use RendersDashboard;

    public string $groom_name = '';

    public string $bride_name = '';

    public string $wedding_date = '';

    public ?string $rsvp_deadline = null;

    public bool $accommodation_enabled = false;

    public string $invitation_locale = '';

    public ?string $send_message = null;

    public ?string $flashMessage = null;

    public function mount(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $this->groom_name = $wedding->groom_name;
        $this->bride_name = $wedding->bride_name;
        $this->wedding_date = $wedding->wedding_date?->format('Y-m-d\TH:i') ?? '';
        $this->rsvp_deadline = $wedding->rsvp_deadline?->format('Y-m-d');
        $this->accommodation_enabled = (bool) $wedding->accommodation_enabled;
        $this->invitation_locale = $wedding->invitation_locale ?? Locale::default();
        $this->send_message = $wedding->send_message;
    }

    public function render()
    {
        return $this->dashboardView('livewire.dashboard.wedding-details', [
            'wedding' => $this->wedding(),
            'locked' => $this->isLocked(),
            'locales' => Locale::options(),
        ], __('dashboard.nav.wedding_details'), [
            ['label' => __('dashboard.nav.wedding'), 'url' => route('dashboard.wedding')],
            ['label' => __('dashboard.nav.wedding_details'), 'url' => null],
        ], backUrl: route('dashboard.wedding'));
    }

    public function save(): void
    {
        $wedding = $this->requireEditableWedding();

        if ($this->rsvp_deadline === '') {
            $this->rsvp_deadline = null;
        }

        if ($this->send_message === '') {
            $this->send_message = null;
        }

        $data = $this->validate([
            'groom_name' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'wedding_date' => ['required', 'date', 'after:now'],
            'rsvp_deadline' => ['nullable', 'date'],
            'accommodation_enabled' => ['boolean'],
            'invitation_locale' => ['required', Rule::in(array_keys(Locale::options()))],
            'send_message' => ['nullable', 'string'],
        ], [
            'wedding_date.after' => __('onboarding.wedding_date_future'),
        ]);

        $wedding->update([
            'groom_name' => $data['groom_name'],
            'bride_name' => $data['bride_name'],
            'wedding_date' => $data['wedding_date'],
            'rsvp_deadline' => $data['rsvp_deadline'] ?: null,
            'accommodation_enabled' => $data['accommodation_enabled'],
            'invitation_locale' => $data['invitation_locale'],
            'send_message' => $data['send_message'] ?: null,
        ]);

        $this->flashMessage = __('dashboard.saved');
    }
}

<?php

namespace App\Livewire\Dashboard;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\WeddingEvent;
use App\Support\Locale;
use App\Support\MediaDisk;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Wedding extends Component
{
    use RendersDashboard;
    use WithFileUploads;

    public string $groom_name = '';

    public string $bride_name = '';

    public string $wedding_date = '';

    public string $theme = '';

    public string $template = '';

    public ?string $reveal_animation = null;

    public ?string $motto = null;

    public ?string $music_url = null;

    public ?string $rsvp_deadline = null;

    public bool $accommodation_enabled = false;

    public string $invitation_locale = '';

    public ?string $send_message = null;

    public $heroUpload = null;

    public bool $removeHero = false;

    public ?string $flashMessage = null;

    public function mount(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $this->groom_name = $wedding->groom_name;
        $this->bride_name = $wedding->bride_name;
        $this->wedding_date = $wedding->wedding_date?->format('Y-m-d\TH:i') ?? '';
        $this->theme = $wedding->theme?->value ?? InvitationTheme::AmberGold->value;
        $this->template = $wedding->template?->value ?? InvitationTemplate::Classic->value;
        $this->reveal_animation = $wedding->reveal_animation?->value;
        $this->motto = $wedding->motto;
        $this->music_url = $wedding->music_url;
        $this->rsvp_deadline = $wedding->rsvp_deadline?->format('Y-m-d');
        $this->accommodation_enabled = (bool) $wedding->accommodation_enabled;
        $this->invitation_locale = $wedding->invitation_locale ?? Locale::default();
        $this->send_message = $wedding->send_message;
    }

    public function render()
    {
        $wedding = $this->wedding();

        return $this->dashboardView('livewire.dashboard.wedding', [
            'wedding' => $wedding,
            'locked' => $this->isLocked(),
            'themes' => InvitationTheme::cases(),
            'templates' => InvitationTemplate::cases(),
            'reveals' => InvitationReveal::cases(),
            'locales' => Locale::options(),
        ], __('dashboard.wedding_title'), [
            ['label' => __('dashboard.nav.wedding'), 'url' => null],
        ]);
    }

    protected function wedding(): ?WeddingEvent
    {
        return auth()->user()?->accessibleWedding();
    }

    public function isLocked(): bool
    {
        $wedding = $this->wedding();

        return $wedding instanceof WeddingEvent && $wedding->isCoupleMutationLocked();
    }

    public function save(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        abort_if($wedding->isCoupleMutationLocked(), 403);

        if ($this->reveal_animation === '') {
            $this->reveal_animation = null;
        }

        if ($this->music_url === '') {
            $this->music_url = null;
        }

        if ($this->rsvp_deadline === '') {
            $this->rsvp_deadline = null;
        }

        if ($this->motto === '') {
            $this->motto = null;
        }

        if ($this->send_message === '') {
            $this->send_message = null;
        }

        $data = $this->validate([
            'groom_name' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'wedding_date' => ['required', 'date', 'after:now'],
            'theme' => ['required', Rule::enum(InvitationTheme::class)],
            'template' => ['required', Rule::enum(InvitationTemplate::class)],
            'reveal_animation' => ['nullable', Rule::enum(InvitationReveal::class)],
            'motto' => ['nullable', 'string', 'max:300'],
            'music_url' => ['nullable', 'url', 'max:500'],
            'rsvp_deadline' => ['nullable', 'date'],
            'accommodation_enabled' => ['boolean'],
            'invitation_locale' => ['required', Rule::in(array_keys(Locale::options()))],
            'send_message' => ['nullable', 'string'],
            'heroUpload' => ['nullable', 'image', 'max:5120'],
        ], [
            'wedding_date.after' => __('onboarding.wedding_date_future'),
        ]);

        $payload = [
            'groom_name' => $data['groom_name'],
            'bride_name' => $data['bride_name'],
            'wedding_date' => $data['wedding_date'],
            'theme' => $data['theme'],
            'template' => $data['template'],
            'reveal_animation' => $data['reveal_animation'] ?: null,
            'motto' => $data['motto'] ?: null,
            'music_url' => $data['music_url'] ?: null,
            'rsvp_deadline' => $data['rsvp_deadline'] ?: null,
            'accommodation_enabled' => $data['accommodation_enabled'],
            'invitation_locale' => $data['invitation_locale'],
            'send_message' => $data['send_message'] ?: null,
        ];

        if ($this->heroUpload instanceof TemporaryUploadedFile) {
            $payload['hero_image'] = $this->heroUpload->store('hero-images', MediaDisk::name());
            $this->heroUpload = null;
            $this->removeHero = false;
        } elseif ($this->removeHero) {
            $payload['hero_image'] = null;
            $this->removeHero = false;
        }

        $wedding->update($payload);

        $this->flashMessage = __('dashboard.saved');
    }

    public function updatedHeroUpload(): void
    {
        $this->removeHero = false;
    }

    public function clearHero(): void
    {
        abort_if($this->isLocked(), 403);

        $this->heroUpload = null;
        $this->removeHero = true;
    }
}

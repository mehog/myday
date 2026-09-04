<?php

namespace App\Livewire\Dashboard;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\Livewire\Dashboard\Concerns\ManagesWeddingSettings;
use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\WeddingEvent;
use App\Support\MediaDisk;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class WeddingDesign extends Component
{
    use ManagesWeddingSettings;
    use RendersDashboard;
    use WithFileUploads;

    public string $theme = '';

    public string $template = '';

    public ?string $reveal_animation = null;

    public ?string $motto = null;

    public ?string $music_url = null;

    public $heroUpload = null;

    public bool $removeHero = false;

    public ?string $flashMessage = null;

    public function mount(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $this->theme = $wedding->theme?->value ?? InvitationTheme::AmberGold->value;
        $this->template = $wedding->template?->value ?? InvitationTemplate::Classic->value;
        $this->reveal_animation = $wedding->reveal_animation?->value;
        $this->motto = $wedding->motto;
        $this->music_url = $wedding->music_url;
    }

    public function render()
    {
        return $this->dashboardView('livewire.dashboard.wedding-design', [
            'wedding' => $this->wedding(),
            'locked' => $this->isLocked(),
            'themes' => InvitationTheme::cases(),
            'templates' => InvitationTemplate::cases(),
            'reveals' => InvitationReveal::cases(),
        ], __('dashboard.nav.wedding_design'), [
            ['label' => __('dashboard.nav.wedding'), 'url' => route('dashboard.wedding')],
            ['label' => __('dashboard.nav.wedding_design'), 'url' => null],
        ], backUrl: route('dashboard.wedding'));
    }

    public function save(): void
    {
        $wedding = $this->requireEditableWedding();

        if ($this->reveal_animation === '') {
            $this->reveal_animation = null;
        }

        if ($this->music_url === '') {
            $this->music_url = null;
        }

        if ($this->motto === '') {
            $this->motto = null;
        }

        $data = $this->validate([
            'theme' => ['required', Rule::enum(InvitationTheme::class)],
            'template' => ['required', Rule::enum(InvitationTemplate::class)],
            'reveal_animation' => ['nullable', Rule::enum(InvitationReveal::class)],
            'motto' => ['nullable', 'string', 'max:300'],
            'music_url' => ['nullable', 'url', 'max:500'],
            'heroUpload' => ['nullable', 'image', 'max:5120'],
        ]);

        $payload = [
            'theme' => $data['theme'],
            'template' => $data['template'],
            'reveal_animation' => $data['reveal_animation'] ?: null,
            'motto' => $data['motto'] ?: null,
            'music_url' => $data['music_url'] ?: null,
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

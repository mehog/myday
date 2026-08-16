<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Support\Locale;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    use RendersDashboard;

    public string $name = '';

    public string $email = '';

    public string $locale = '';

    public ?string $flashMessage = null;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->locale = Locale::resolve($user->locale);
    }

    public function render()
    {
        return $this->dashboardView('livewire.dashboard.profile', [
            'locales' => Locale::options(),
        ], __('dashboard.profile_title'), [
            ['label' => __('dashboard.nav.profile'), 'url' => null],
        ]);
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['required', Rule::in(array_keys(Locale::options()))],
        ]);

        $user->update([
            'name' => $data['name'],
            'locale' => $data['locale'],
        ]);

        session(['locale' => $data['locale']]);
        Locale::apply($data['locale']);

        $this->flashMessage = __('dashboard.profile_saved');
    }
}

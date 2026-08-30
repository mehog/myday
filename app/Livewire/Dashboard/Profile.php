<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Support\Locale;
use App\Support\UnverifiedEmail;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use NotificationChannels\WebPush\PushSubscription;

class Profile extends Component
{
    use RendersDashboard;

    public string $name = '';

    public string $email = '';

    public string $locale = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

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
        $user = auth()->user();
        abort_unless($user !== null, 403);

        return $this->dashboardView('livewire.dashboard.profile', [
            'locales' => Locale::options(),
            'devices' => $this->devices(),
            'emailEditable' => ! $user->hasVerifiedEmail(),
        ], __('dashboard.profile_title'), [
            ['label' => __('dashboard.nav.profile'), 'url' => null],
        ], backUrl: route('dashboard.more'));
    }

    /**
     * @return Collection<int, PushSubscription>
     */
    protected function devices(): Collection
    {
        $user = auth()->user();

        if ($user === null) {
            return collect();
        }

        return $user->pushSubscriptions()->orderByDesc('created_at')->get();
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $changingPassword = $this->password !== '' || $this->current_password !== '' || $this->password_confirmation !== '';

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['required', Rule::in(array_keys(Locale::options()))],
        ];

        if ($changingPassword) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        if (! $user->hasVerifiedEmail()) {
            $rules['email'] = UnverifiedEmail::rules($user)['email'];
        }

        $data = $this->validate($rules, [
            'email.unique' => __('onboarding.verify_email_taken'),
        ]);

        if (! $user->hasVerifiedEmail() && strcasecmp($data['email'], $user->email) !== 0) {
            UnverifiedEmail::update($user, $data['email']);
            $user->refresh();
            $this->email = $user->email;
        }

        $payload = [
            'name' => $data['name'],
            'locale' => $data['locale'],
        ];

        if ($changingPassword) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        session(['locale' => $data['locale']]);
        Locale::apply($data['locale']);

        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';

        $this->flashMessage = __('dashboard.profile_saved');
    }

    public function removeDevice(int $id): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $subscription = $user->pushSubscriptions()->whereKey($id)->first();

        if ($subscription === null || ! $user->ownsPushSubscription($subscription)) {
            return;
        }

        $subscription->delete();

        $this->flashMessage = __('app.push_devices_removed');
    }
}

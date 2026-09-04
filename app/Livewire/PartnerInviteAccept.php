<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\WeddingPartnerInvite;
use App\Services\WeddingPartnerInviteService;
use App\Support\DashboardNav;
use App\Support\Locale;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.onboarding')]
class PartnerInviteAccept extends Component
{
    public string $token = '';

    public ?WeddingPartnerInvite $invite = null;

    public string $mode = 'register';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $errorMessage = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->invite = WeddingPartnerInvite::query()
            ->with(['weddingEvent', 'invitedBy'])
            ->where('token', $token)
            ->first();

        if ($this->invite === null) {
            $this->errorMessage = __('dashboard.partner_invite_invalid');

            return;
        }

        if ($this->invite->accepted_at !== null) {
            if (Auth::check() && Auth::user()?->isPartnerOf($this->invite->weddingEvent)) {
                $this->redirect(DashboardNav::homeUrl());

                return;
            }

            $this->errorMessage = __('dashboard.partner_invite_already_accepted');

            return;
        }

        if ($this->invite->isExpired()) {
            $this->errorMessage = __('dashboard.partner_invite_expired');

            return;
        }

        if (filled($this->invite->email)) {
            $this->email = $this->invite->email;
        }

        if (Auth::check()) {
            $this->mode = 'accept';
        }
    }

    public function switchLocale(string $locale): void
    {
        Locale::set($locale, persistToUser: false);
    }

    public function setMode(string $mode): void
    {
        if (in_array($mode, ['register', 'login'], true)) {
            $this->mode = $mode;
            $this->errorMessage = null;
        }
    }

    public function register(WeddingPartnerInviteService $service): void
    {
        $this->errorMessage = null;

        if ($this->invite === null || ! $this->invite->isUsable()) {
            $this->errorMessage = __('dashboard.partner_invite_expired');

            return;
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if (filled($this->invite->email)) {
            $rules['email'] = ['required', 'email', 'max:255', 'in:'.$this->invite->email];
        } else {
            $rules['email'] = ['required', 'email', 'max:255', 'unique:users,email'];
        }

        $data = $this->validate($rules);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => $data['password'],
            'is_admin' => false,
            'locale' => Locale::canonicalize(Locale::current()),
        ]);

        event(new Registered($user));
        Auth::login($user);
        $user->sendEmailVerificationNotification();

        try {
            $service->acceptInvite($this->invite, $user);
        } catch (ValidationException $exception) {
            Auth::logout();
            $user->delete();
            throw $exception;
        }

        $this->redirect(DashboardNav::homeUrl());
    }

    public function login(WeddingPartnerInviteService $service): void
    {
        $this->errorMessage = null;

        if ($this->invite === null || ! $this->invite->isUsable()) {
            $this->errorMessage = __('dashboard.partner_invite_expired');

            return;
        }

        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];

        if (filled($this->invite->email)) {
            $rules['email'][] = 'in:'.$this->invite->email;
        }

        $credentials = $this->validate($rules);

        if (! Auth::attempt([
            'email' => strtolower($credentials['email']),
            'password' => $credentials['password'],
        ], remember: true)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $service->acceptInvite($this->invite, $user);

        $this->redirect(DashboardNav::homeUrl());
    }

    public function accept(WeddingPartnerInviteService $service): void
    {
        $this->errorMessage = null;

        if ($this->invite === null || ! $this->invite->isUsable()) {
            $this->errorMessage = __('dashboard.partner_invite_expired');

            return;
        }

        /** @var User|null $user */
        $user = Auth::user();

        if ($user === null) {
            $this->mode = 'login';

            return;
        }

        $service->acceptInvite($this->invite, $user);

        $this->redirect(DashboardNav::homeUrl());
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->mode = 'login';
    }

    public function render()
    {
        return view('livewire.partner-invite-accept', [
            'wedding' => $this->invite?->weddingEvent,
            'inviter' => $this->invite?->invitedBy,
        ]);
    }
}

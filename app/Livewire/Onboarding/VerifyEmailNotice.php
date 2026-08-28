<?php

namespace App\Livewire\Onboarding;

use App\Support\DashboardNav;
use App\Support\Locale;
use App\Support\UnverifiedEmail;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.onboarding')]
class VerifyEmailNotice extends Component
{
    public bool $resent = false;

    public bool $updated = false;

    public string $email = '';

    public function mount(): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->redirectRoute('onboarding');

            return;
        }

        if ($user->hasVerifiedEmail()) {
            $this->redirect(DashboardNav::homeUrl());

            return;
        }

        if ($user->hasEmailVerificationGrace()) {
            $this->redirect(DashboardNav::homeUrl());

            return;
        }

        $this->email = $user->email;
    }

    public function switchLocale(string $locale): void
    {
        Locale::set($locale);
    }

    public function resend(): void
    {
        $user = Auth::user();

        if ($user === null || $user->hasVerifiedEmail()) {
            return;
        }

        UnverifiedEmail::resend($user);
        $this->resent = true;
        $this->updated = false;
    }

    public function updateEmail(): void
    {
        $user = Auth::user();

        if ($user === null || $user->hasVerifiedEmail()) {
            return;
        }

        if (UnverifiedEmail::update($user, $this->email)) {
            $this->updated = true;
            $this->resent = false;
        }

        $this->email = $user->fresh()->email;
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirectRoute('home');
    }

    public function render()
    {
        return view('livewire.onboarding.verify-email-notice')
            ->title(__('onboarding.verify_title'));
    }
}

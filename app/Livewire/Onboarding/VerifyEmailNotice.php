<?php

namespace App\Livewire\Onboarding;

use App\Support\Locale;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.onboarding')]
class VerifyEmailNotice extends Component
{
    public bool $resent = false;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->redirectRoute('onboarding');

            return;
        }

        if ($user->hasVerifiedEmail()) {
            $this->redirect('/app');
        }
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

        $user->sendEmailVerificationNotification();
        $this->resent = true;
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

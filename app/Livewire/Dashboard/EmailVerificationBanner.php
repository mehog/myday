<?php

namespace App\Livewire\Dashboard;

use App\Support\UnverifiedEmail;
use Livewire\Component;

class EmailVerificationBanner extends Component
{
    public string $email = '';

    public bool $showForm = false;

    public bool $resent = false;

    public bool $updated = false;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user !== null) {
            $this->email = $user->email;
        }
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
    }

    public function resend(): void
    {
        $user = auth()->user();

        if ($user === null || $user->hasVerifiedEmail()) {
            return;
        }

        UnverifiedEmail::resend($user);
        $this->resent = true;
        $this->updated = false;
    }

    public function updateEmail(): void
    {
        $user = auth()->user();

        if ($user === null || $user->hasVerifiedEmail()) {
            return;
        }

        if (UnverifiedEmail::update($user, $this->email)) {
            $this->updated = true;
            $this->resent = false;
        }

        $this->showForm = false;
        $this->email = $user->fresh()->email;
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.dashboard.email-verification-banner', [
            'graceExpiresAt' => $user?->emailVerificationGraceExpiresAt(),
        ]);
    }
}

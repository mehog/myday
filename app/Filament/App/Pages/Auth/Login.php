<?php

namespace App\Filament\App\Pages\Auth;

use App\Support\DashboardNav;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;

class Login extends BaseLogin
{
    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(DashboardNav::homeUrl());
        }

        $this->form->fill();
    }
}

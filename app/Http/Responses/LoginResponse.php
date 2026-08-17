<?php

namespace App\Http\Responses;

use App\Support\DashboardNav;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        if (Filament::getCurrentPanel()?->getId() === 'app') {
            return redirect()->intended(DashboardNav::homeUrl());
        }

        return redirect()->intended(Filament::getUrl());
    }
}

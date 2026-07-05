<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ReferralLinkController extends Controller
{
    public function __invoke(Request $request, string $code): RedirectResponse
    {
        Cookie::queue(
            config('referral.cookie_name'),
            $code,
            (int) config('referral.cookie_expiry'),
        );

        return redirect()->route(config('referral.redirect_route'));
    }
}

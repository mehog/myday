<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ReferralProgramController extends Controller
{
    public function __invoke(): View
    {
        $fee = (float) config('referral.default_fee', 10);

        return view('landing.referral-program', [
            'fee' => $fee,
            'pageTitle' => __('referrals.page_title'),
            'pageDescription' => __('referrals.page_subheading', [
                'fee' => number_format($fee, 0),
            ]),
            'canonicalUrl' => route('referral-program'),
        ]);
    }
}

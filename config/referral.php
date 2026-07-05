<?php

use App\Models\User;

return [

    'default_fee' => 10,

    'cookie_name' => 'nasdan_ref',

    'cookie_expiry' => 525600,

    'route_prefix' => 'ref',

    'ref_code_prefix' => '_',

    'redirect_route' => 'onboarding',

    'user_model' => User::class,

    'referral_length' => 8,

];

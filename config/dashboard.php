<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Couple dashboard v2
    |--------------------------------------------------------------------------
    |
    | When enabled, verified couples land on /dashboard after login instead of
    | the Filament /app panel. Filament /app remains available as a fallback.
    |
    */

    'default' => (bool) env('DASHBOARD_V2_DEFAULT', true),

];

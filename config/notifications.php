<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin notification recipients
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of email addresses to notify when no admin users
    | exist in the database. Admin users (is_admin = true) are always notified.
    |
    */

    'admin_emails' => array_filter(array_map(
        trim(...),
        explode(',', (string) env('ADMIN_NOTIFICATION_EMAILS', '')),
    )),

    /*
    |--------------------------------------------------------------------------
    | Couple onboarding drip (hours after signup)
    |--------------------------------------------------------------------------
    |
    | Keys are content variants (day1/day3/day7) kept for translation keys.
    | Values are hours after the user's exact registration timestamp.
    |
    */

    'couple_onboarding_hours' => [
        'day1' => 6,
        'day3' => 18,
        'day7' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Hours after signup to remind couple their invitation is still inactive
    |--------------------------------------------------------------------------
    */

    'couple_activation_reminder_hours' => (int) env('COUPLE_ACTIVATION_REMINDER_HOURS', 42),

    /*
    |--------------------------------------------------------------------------
    | Days before wedding_date to alert admins about an inactive invitation
    |--------------------------------------------------------------------------
    */

    'admin_inactive_wedding_days_before' => (int) env('ADMIN_INACTIVE_WEDDING_DAYS_BEFORE', 14),

    /*
    |--------------------------------------------------------------------------
    | Seconds to wait between sends in notifications:preview (rate-limit safety)
    |--------------------------------------------------------------------------
    */

    'preview_delay_seconds' => (int) env('NOTIFICATION_PREVIEW_DELAY_SECONDS', 2),

];

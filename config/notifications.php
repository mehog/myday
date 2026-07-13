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
    | Couple onboarding drip (days after signup)
    |--------------------------------------------------------------------------
    */

    'couple_onboarding_days' => [1, 3, 7],

    /*
    |--------------------------------------------------------------------------
    | Days after signup to remind couple their invitation is still inactive
    |--------------------------------------------------------------------------
    */

    'couple_activation_reminder_day' => (int) env('COUPLE_ACTIVATION_REMINDER_DAY', 7),

    /*
    |--------------------------------------------------------------------------
    | Days before wedding_date to alert admins about an inactive invitation
    |--------------------------------------------------------------------------
    */

    'admin_inactive_wedding_days_before' => (int) env('ADMIN_INACTIVE_WEDDING_DAYS_BEFORE', 14),

    /*
    |--------------------------------------------------------------------------
    | Days after an enquiry to send admin a follow-up reminder
    |--------------------------------------------------------------------------
    */

    'admin_enquiry_follow_up_days' => (int) env('ADMIN_ENQUIRY_FOLLOW_UP_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Seconds to wait between sends in notifications:preview (rate-limit safety)
    |--------------------------------------------------------------------------
    */

    'preview_delay_seconds' => (int) env('NOTIFICATION_PREVIEW_DELAY_SECONDS', 2),

];

<?php

$domain = env('LEGAL_DOMAIN', 'nasdan.app');
$websiteUrl = rtrim(env('LEGAL_WEBSITE_URL', 'https://'.$domain), '/');
$supportEmail = env('LEGAL_SUPPORT_EMAIL', 'info@nasdan.ba');
$brandName = env('LEGAL_BRAND_NAME', env('APP_NAME', 'NasDan'));

return [

    /*
    |--------------------------------------------------------------------------
    | Merchant / operator details
    |--------------------------------------------------------------------------
    |
    | NasDan is operated by an individual (no company/LLC). The product lives on
    | nasdan.app; support mail currently uses nasdan.ba (info@nasdan.ba).
    | Leave LEGAL_OPERATOR_ADDRESS empty to use the localized default that
    | references website_url.
    |
    */

    'brand_name' => $brandName,

    'domain' => $domain,

    'website_url' => $websiteUrl,

    'operator_name' => env('LEGAL_OPERATOR_NAME', $brandName),

    'operator_address' => env('LEGAL_OPERATOR_ADDRESS'),

    'jurisdiction' => env('LEGAL_JURISDICTION', 'Bosnia and Herzegovina'),

    'support_email' => $supportEmail,

    'effective_date' => env('LEGAL_EFFECTIVE_DATE', '2026-07-28'),

    'refund_window_days' => (int) env('LEGAL_REFUND_WINDOW_DAYS', 7),

    /*
    | Months of post-closure retention referenced by Privacy Policy translations.
    */
    'data_retention_months' => (int) env('LEGAL_DATA_RETENTION_MONTHS', 24),

];

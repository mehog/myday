<?php

namespace App\Support;

use App\Models\DiscountCode;

final class DiscountEmailPlaceholders
{
    /**
     * @return array<string, string>
     */
    public static function for(?DiscountCode $code, string $name, ?string $locale = null): array
    {
        $resolvedLocale = Locale::resolve($locale);
        $previousLocale = app()->getLocale();
        Locale::apply($resolvedLocale);

        try {
            $hasExpiry = $code?->expires_at !== null;
            $date = $hasExpiry
                ? $code->expires_at->timezone(config('app.timezone'))->format('Y-m-d')
                : null;

            return [
                '{{code}}' => $code?->code ?? 'SAMPLE15',
                '{{discount_label}}' => $code?->discountLabel() ?? '15%',
                '{{name}}' => $name,
                '{{expires}}' => $hasExpiry
                    ? (string) $date
                    : __('discounts.expires_never'),
                '{{expires_clause}}' => $hasExpiry
                    ? __('discounts.expires_clause', ['date' => $date])
                    : '',
            ];
        } finally {
            Locale::apply($previousLocale);
        }
    }

    public static function apply(string $text, array $replacements): string
    {
        $rendered = strtr($text, $replacements);

        // Safety net if an older worker/code path missed the key.
        $rendered = str_replace('{{expires_clause}}', '', $rendered);

        // Clean up gaps left by an empty {{expires_clause}}.
        $rendered = preg_replace('/[ \t]{2,}/', ' ', $rendered) ?? $rendered;
        $rendered = preg_replace('/ +([.,!?;:])/', '$1', $rendered) ?? $rendered;
        $rendered = preg_replace('/ +\n/', "\n", $rendered) ?? $rendered;
        $rendered = preg_replace('/\n{3,}/', "\n\n", $rendered) ?? $rendered;

        return trim($rendered);
    }
}

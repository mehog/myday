<?php

namespace App\Support;

class LocaleUrl
{
    public static function withLocale(string $url, ?string $locale = null): string
    {
        $locale ??= Locale::current();

        if (! Locale::isSupported($locale)) {
            return $url;
        }

        [$base, $existingQuery] = array_pad(explode('?', $url, 2), 2, '');
        parse_str($existingQuery, $query);
        $query['locale'] = $locale;

        return $base.'?'.http_build_query($query);
    }
}

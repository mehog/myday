<?php

namespace App\Support;

class LandingAsset
{
    /**
     * Resolve a landing screenshot path for the active locale, falling back to Bosnian.
     */
    public static function path(string $filename): string
    {
        $filename = ltrim($filename, '/');
        $locale = app()->getLocale();

        $localized = "img/landing/{$locale}/{$filename}";
        if (is_file(public_path($localized))) {
            return $localized;
        }

        $bosnian = "img/landing/bs/{$filename}";
        if (is_file(public_path($bosnian))) {
            return $bosnian;
        }

        // Legacy flat assets used before locale folders existed.
        $legacy = "img/landing/{$filename}";
        if (is_file(public_path($legacy))) {
            return $legacy;
        }

        return $bosnian;
    }
}

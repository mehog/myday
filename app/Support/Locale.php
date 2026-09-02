<?php

namespace App\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Locale
{
    /**
     * Accept-Language / query aliases that resolve to a supported locale.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'sr' => 'sr_Latn',
        'sr_rs' => 'sr_Latn',
        'sr_latn' => 'sr_Latn',
        'sr_latn_rs' => 'sr_Latn',
        'sr_cyrl' => 'sr_Latn',
        'sr_cyrl_rs' => 'sr_Latn',
        // ISO country codes used as ipstack country_code fallback.
        'ba' => 'bs',
        'rs' => 'sr_Latn',
    ];

    /**
     * @return list<string>
     */
    public static function supported(): array
    {
        return config('app.supported_locales', ['en', 'bs']);
    }

    public static function default(): string
    {
        return config('app.default_locale', 'en');
    }

    public static function canonicalize(?string $locale): ?string
    {
        if (! is_string($locale) || $locale === '') {
            return null;
        }

        $normalized = strtolower(str_replace('-', '_', $locale));

        if (isset(self::ALIASES[$normalized])) {
            return self::ALIASES[$normalized];
        }

        foreach (self::supported() as $supported) {
            if (strtolower($supported) === $normalized) {
                return $supported;
            }
        }

        $short = explode('_', $normalized)[0];

        foreach (self::supported() as $supported) {
            if (strtolower($supported) === $short) {
                return $supported;
            }
        }

        return null;
    }

    public static function isSupported(string $locale): bool
    {
        return self::canonicalize($locale) !== null;
    }

    public static function resolve(?string $locale): string
    {
        return self::canonicalize($locale) ?? self::default();
    }

    public static function current(): string
    {
        return App::getLocale();
    }

    public static function htmlLang(?string $locale = null): string
    {
        $resolved = $locale === null
            ? self::current()
            : (self::canonicalize($locale) ?? $locale);

        return str_replace('_', '-', $resolved);
    }

    public static function set(string $locale, bool $persistToUser = true): bool
    {
        $canonical = self::canonicalize($locale);

        if ($canonical === null) {
            return false;
        }

        session(['locale' => $canonical]);

        if ($persistToUser) {
            $user = auth()->user();
            if ($user instanceof User) {
                $user->update(['locale' => $canonical]);
            }
        }

        self::apply($canonical);

        return true;
    }

    public static function resolveFromRequest(Request $request): string
    {
        $queryLocale = $request->query('locale');

        if (is_string($queryLocale)) {
            $canonical = self::canonicalize($queryLocale);

            if ($canonical !== null) {
                session(['locale' => $canonical]);

                return $canonical;
            }
        }

        $user = auth()->user();
        if ($user instanceof User && $user->locale) {
            $canonical = self::canonicalize($user->locale);

            if ($canonical !== null) {
                session(['locale' => $canonical]);

                return $canonical;
            }
        }

        $sessionLocale = session('locale');

        if (is_string($sessionLocale)) {
            $canonical = self::canonicalize($sessionLocale);

            if ($canonical !== null) {
                return $canonical;
            }
        }

        $accepted = self::fromAcceptLanguage($request);

        if ($accepted !== null) {
            return $accepted;
        }

        return self::default();
    }

    public static function fromAcceptLanguage(Request $request): ?string
    {
        foreach ($request->getLanguages() as $language) {
            $canonical = self::canonicalize($language);

            if ($canonical !== null) {
                return $canonical;
            }
        }

        return null;
    }

    /**
     * Derive a supported app locale from an ipstack Location-module payload.
     *
     * Prefers location.languages[].code, then falls back to country_code.
     * Returns null when nothing maps to a supported locale.
     */
    public static function fromIpstack(?object $ipstack): ?string
    {
        if ($ipstack === null) {
            return null;
        }

        $languages = $ipstack->location->languages ?? null;

        if (is_array($languages) || $languages instanceof \Traversable) {
            foreach ($languages as $language) {
                $code = is_object($language) ? ($language->code ?? null) : null;

                if (! is_string($code) || $code === '') {
                    continue;
                }

                $canonical = self::canonicalize($code);

                if ($canonical !== null) {
                    return $canonical;
                }
            }
        }

        $countryCode = $ipstack->country_code ?? null;

        if (is_string($countryCode) && $countryCode !== '') {
            return self::canonicalize($countryCode);
        }

        return null;
    }

    public static function apply(string $locale): void
    {
        $canonical = self::canonicalize($locale) ?? $locale;

        App::setLocale($canonical);
        Carbon::setLocale($canonical);
    }

    public static function ogLocale(): string
    {
        return match (self::current()) {
            'bs' => 'bs_BA',
            'de' => 'de_DE',
            'hr' => 'hr_HR',
            'sr_Latn' => 'sr_RS',
            default => 'en_US',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $labels = config('app.locale_labels', []);

        return array_intersect_key(
            $labels,
            array_flip(self::supported()),
        );
    }
}

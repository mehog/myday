<?php

namespace App\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Locale
{
    /**
     * @return list<string>
     */
    public static function supported(): array
    {
        return config('app.supported_locales', ['en', 'bs']);
    }

    public static function default(): string
    {
        return config('app.default_locale', 'bs');
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::supported(), true);
    }

    public static function current(): string
    {
        return App::getLocale();
    }

    public static function set(string $locale): bool
    {
        if (! self::isSupported($locale)) {
            return false;
        }

        session(['locale' => $locale]);

        $user = auth()->user();
        if ($user instanceof User) {
            $user->update(['locale' => $locale]);
        }

        self::apply($locale);

        return true;
    }

    public static function resolveFromRequest(Request $request): string
    {
        $queryLocale = $request->query('locale');

        if (is_string($queryLocale) && self::isSupported($queryLocale)) {
            session(['locale' => $queryLocale]);

            $user = auth()->user();
            if ($user instanceof User) {
                $user->update(['locale' => $queryLocale]);
            }

            return $queryLocale;
        }

        $user = auth()->user();
        if ($user instanceof User && $user->locale && self::isSupported($user->locale)) {
            session(['locale' => $user->locale]);

            return $user->locale;
        }

        $sessionLocale = session('locale', self::default());

        if (self::isSupported($sessionLocale)) {
            return $sessionLocale;
        }

        return self::default();
    }

    public static function apply(string $locale): void
    {
        App::setLocale($locale);
        Carbon::setLocale($locale);
    }

    public static function ogLocale(): string
    {
        return match (self::current()) {
            'bs' => 'bs_BA',
            'de' => 'de_DE',
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

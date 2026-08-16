<?php

namespace App\Support;

use Illuminate\Support\Collection;

class OnboardingSongs
{
    /**
     * @return list<array{id: string, title: string, artist: string, url: string}>
     */
    public static function forLocale(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $groups = config('onboarding.songs', []);

        if (! is_array($groups)) {
            return [];
        }

        $songs = [];

        if (self::includesRegional($locale)) {
            $songs = array_merge($songs, self::groupSongs($groups, 'regional'));
        }

        $songs = array_merge(
            $songs,
            self::groupSongs($groups, 'international'),
            self::groupSongs($groups, 'german'),
        );

        return array_values($songs);
    }

    public static function includesRegional(?string $locale = null): bool
    {
        $locale ??= app()->getLocale();
        $regionalLocales = config('onboarding.regional_song_locales', ['bs', 'hr']);

        return is_array($regionalLocales) && in_array($locale, $regionalLocales, true);
    }

    /**
     * @param  array<string, mixed>  $groups
     * @return list<array{id: string, title: string, artist: string, url: string}>
     */
    private static function groupSongs(array $groups, string $key): array
    {
        $songs = $groups[$key] ?? [];

        if (! is_array($songs)) {
            return [];
        }

        return array_values(array_filter(
            $songs,
            fn ($song): bool => is_array($song)
                && isset($song['id'], $song['title'], $song['artist'], $song['url']),
        ));
    }

    /**
     * @return Collection<int, array{id: string, title: string, artist: string, url: string}>
     */
    public static function catalog(?string $locale = null): Collection
    {
        return collect(self::forLocale($locale));
    }
}

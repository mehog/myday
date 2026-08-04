<?php

namespace App\Support;

use App\Models\WeddingEvent;

class DemoInvitationUrl
{
    public static function make(
        string $slug,
        string $theme,
        string $template,
        string $reveal = '',
        ?string $locale = null,
        ?string $guestToken = null,
    ): string {
        $url = $guestToken
            ? route('invitation.guest', [$slug, $guestToken])
            : route('invitation.show', $slug);

        $url = LocaleUrl::withLocale($url, $locale);

        [$base, $existingQuery] = array_pad(explode('?', $url, 2), 2, '');
        parse_str($existingQuery, $query);

        $query['theme'] = $theme;
        $query['template'] = $template;
        $query['reveal'] = $reveal === '' ? 'none' : $reveal;

        return $base.'?'.http_build_query($query);
    }

    /**
     * @param  array{theme: string, template: string, reveal: string}  $example
     * @return array{title: string, previewUrl: string, openUrl: string}
     */
    public static function fromExample(
        array $example,
        string $slug,
        ?string $locale = null,
        ?string $guestToken = null,
    ): array {
        $reveal = $example['reveal'] !== 'envelope' && $example['reveal'] !== 'curtain' ? 'none' : $example['reveal'];
        return [
            'title' => DemoInvitationExamples::title($example),
            'previewUrl' => self::make($slug, $example['theme'], $example['template'], $reveal, $locale, $guestToken),
            'openUrl' => self::make($slug, $example['theme'], $example['template'], $example['reveal'], $locale, $guestToken),
        ];
    }

    /**
     * @return array{slug: string, guestToken: string|null}
     */
    public static function resolveDemoHost(?string $locale = null): array
    {
        $slug = DemoInvitationExamples::demoSlug($locale);

        $event = WeddingEvent::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['guests' => fn ($q) => $q->limit(1)])
            ->first();

        return [
            'slug' => $slug,
            'guestToken' => $event?->guests->first()?->token,
        ];
    }

    public static function onboarding(
        string $theme,
        string $template,
        string $reveal = '',
        ?string $locale = null,
    ): string {
        $url = LocaleUrl::withLocale(route('onboarding'), $locale);

        [$base, $existingQuery] = array_pad(explode('?', $url, 2), 2, '');
        parse_str($existingQuery, $query);

        $query['theme'] = $theme;
        $query['template'] = $template;
        $query['reveal'] = $reveal === '' ? 'none' : $reveal;

        return $base.'?'.http_build_query($query);
    }
}

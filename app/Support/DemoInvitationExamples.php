<?php

namespace App\Support;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;

class DemoInvitationExamples
{
    /**
     * @return list<array{theme: string, template: string, reveal: string}>
     */
    public static function featured(): array
    {
        return [
            ['theme' => 'amber-gold', 'template' => 'classic', 'reveal' => ''],
            ['theme' => 'royal-wedding', 'template' => 'editorial', 'reveal' => 'envelope'],
            ['theme' => 'lavender-dream', 'template' => 'story', 'reveal' => 'storybook'],
            ['theme' => 'winter-magic', 'template' => 'classic', 'reveal' => 'curtain'],
            ['theme' => 'dusty-rose', 'template' => 'editorial', 'reveal' => 'wax-seal'],
        ];
    }

    /**
     * @return list<array{theme: string, template: string, reveal: string}>
     */
    public static function gallery(): array
    {
        return [
            ['theme' => 'amber-gold', 'template' => 'classic', 'reveal' => ''],
            ['theme' => 'royal-wedding', 'template' => 'editorial', 'reveal' => 'envelope'],
            ['theme' => 'lavender-dream', 'template' => 'story', 'reveal' => 'storybook'],
            ['theme' => 'winter-magic', 'template' => 'classic', 'reveal' => 'curtain'],
            ['theme' => 'dusty-rose', 'template' => 'editorial', 'reveal' => 'wax-seal'],
            ['theme' => 'pearl-white', 'template' => 'classic', 'reveal' => 'garden-gate'],
            ['theme' => 'paper-ink', 'template' => 'editorial', 'reveal' => 'sunrise-bloom'],
            ['theme' => 'amber-gold', 'template' => 'story', 'reveal' => 'royal-crest-doors'],
            ['theme' => 'royal-wedding', 'template' => 'classic', 'reveal' => 'storybook'],
            ['theme' => 'lavender-dream', 'template' => 'editorial', 'reveal' => 'envelope'],
            ['theme' => 'winter-magic', 'template' => 'story', 'reveal' => 'wax-seal'],
            ['theme' => 'dusty-rose', 'template' => 'classic', 'reveal' => 'curtain'],
            ['theme' => 'pearl-white', 'template' => 'editorial', 'reveal' => ''],
            ['theme' => 'paper-ink', 'template' => 'classic', 'reveal' => 'garden-gate'],
            ['theme' => 'amber-gold', 'template' => 'editorial', 'reveal' => 'sunrise-bloom'],
            ['theme' => 'royal-wedding', 'template' => 'story', 'reveal' => 'royal-crest-doors'],
            ['theme' => 'lavender-dream', 'template' => 'classic', 'reveal' => 'wax-seal'],
            ['theme' => 'winter-magic', 'template' => 'editorial', 'reveal' => 'envelope'],
            ['theme' => 'dusty-rose', 'template' => 'story', 'reveal' => 'garden-gate'],
            ['theme' => 'pearl-white', 'template' => 'story', 'reveal' => 'curtain'],
        ];
    }

    /**
     * @param  array{theme: string, template: string, reveal: string}  $example
     */
    public static function title(array $example): string
    {
        $theme = InvitationTheme::from($example['theme'])->label();
        $template = InvitationTemplate::from($example['template'])->label();
        $reveal = $example['reveal'] === ''
            ? __('app.reveal_none')
            : InvitationReveal::from($example['reveal'])->label();

        return "{$theme} – {$template} – {$reveal}";
    }

    public static function demoSlug(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $suffix = $locale !== 'bs' ? '-'.$locale : '';

        return 'demo-islamsko'.$suffix;
    }
}

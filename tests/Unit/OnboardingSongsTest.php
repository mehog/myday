<?php

namespace Tests\Unit;

use App\Support\OnboardingSongs;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OnboardingSongsTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function localeProvider(): array
    {
        return [
            'bosnian' => ['bs', true],
            'croatian' => ['hr', true],
            'serbian' => ['sr_Latn', true],
            'english' => ['en', false],
            'german' => ['de', false],
        ];
    }

    #[DataProvider('localeProvider')]
    public function test_regional_songs_follow_locale(string $locale, bool $expectsRegional): void
    {
        $catalog = OnboardingSongs::forLocale($locale);
        $ids = collect($catalog)->pluck('id');

        $this->assertSame($expectsRegional, $ids->contains('DBql7oBK-fs'));
        $this->assertTrue($ids->contains('2Vv-BfVoq4g'));
        $this->assertTrue($ids->contains('LyYAQHDMqfA'));
        $this->assertSame($expectsRegional, OnboardingSongs::includesRegional($locale));
    }
}

<?php

namespace Tests\Unit;

use App\Support\Locale;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    public function test_serbian_aliases_canonicalize_to_latin(): void
    {
        foreach (['sr', 'sr-RS', 'sr_RS', 'sr-Latn', 'sr_Latn', 'sr-latn', 'sr-Cyrl', 'sr_Cyrl'] as $alias) {
            $this->assertSame('sr_Latn', Locale::canonicalize($alias), $alias);
            $this->assertTrue(Locale::isSupported($alias), $alias);
            $this->assertSame('sr_Latn', Locale::resolve($alias), $alias);
        }
    }

    public function test_html_lang_uses_bcp47_hyphens(): void
    {
        $this->assertSame('sr-Latn', Locale::htmlLang('sr_Latn'));
        $this->assertSame('sr-Latn', Locale::htmlLang('sr'));
        $this->assertSame('en', Locale::htmlLang('en'));
        $this->assertSame('de', Locale::htmlLang('de-DE'));
    }

    public function test_og_locale_for_serbian_latin(): void
    {
        Locale::apply('sr_Latn');

        $this->assertSame('sr_RS', Locale::ogLocale());
    }
}

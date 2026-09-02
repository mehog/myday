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

    public function test_from_ipstack_prefers_language_code(): void
    {
        $payload = (object) [
            'country_code' => 'US',
            'location' => (object) [
                'languages' => [
                    (object) ['code' => 'en', 'name' => 'English'],
                ],
            ],
        ];

        $this->assertSame('en', Locale::fromIpstack($payload));
    }

    public function test_from_ipstack_maps_supported_language_codes(): void
    {
        foreach ([
            'en' => 'en',
            'de' => 'de',
            'hr' => 'hr',
            'bs' => 'bs',
            'sr' => 'sr_Latn',
        ] as $code => $expected) {
            $payload = (object) [
                'country_code' => 'XX',
                'location' => (object) [
                    'languages' => [
                        (object) ['code' => $code],
                    ],
                ],
            ];

            $this->assertSame($expected, Locale::fromIpstack($payload), $code);
        }
    }

    public function test_from_ipstack_falls_back_to_country_code(): void
    {
        foreach ([
            'DE' => 'de',
            'HR' => 'hr',
            'BA' => 'bs',
            'RS' => 'sr_Latn',
        ] as $country => $expected) {
            $payload = (object) [
                'country_code' => $country,
            ];

            $this->assertSame($expected, Locale::fromIpstack($payload), $country);
        }
    }

    public function test_from_ipstack_skips_unsupported_language_then_uses_next(): void
    {
        $payload = (object) [
            'country_code' => 'US',
            'location' => (object) [
                'languages' => [
                    (object) ['code' => 'fr'],
                    (object) ['code' => 'de'],
                ],
            ],
        ];

        $this->assertSame('de', Locale::fromIpstack($payload));
    }

    public function test_from_ipstack_falls_back_to_country_when_languages_unsupported(): void
    {
        $payload = (object) [
            'country_code' => 'DE',
            'location' => (object) [
                'languages' => [
                    (object) ['code' => 'fr'],
                ],
            ],
        ];

        $this->assertSame('de', Locale::fromIpstack($payload));
    }

    public function test_from_ipstack_returns_null_for_unmatched_country_without_languages(): void
    {
        $payload = (object) [
            'country_code' => 'US',
        ];

        $this->assertNull(Locale::fromIpstack($payload));
    }

    public function test_from_ipstack_returns_null_for_null_payload(): void
    {
        $this->assertNull(Locale::fromIpstack(null));
    }

    public function test_country_aliases_canonicalize(): void
    {
        $this->assertSame('bs', Locale::canonicalize('BA'));
        $this->assertSame('sr_Latn', Locale::canonicalize('RS'));
    }
}

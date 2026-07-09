<?php

namespace Tests\Unit;

use App\Support\PdfEmoji;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PdfEmojiTest extends TestCase
{
    private const FIXTURE_HEX = '1f338';

    protected function setUp(): void
    {
        parent::setUp();

        $fixturePath = storage_path('emoji/twemoji/72x72/'.self::FIXTURE_HEX.'.png');

        File::ensureDirectoryExists(dirname($fixturePath));
        File::put($fixturePath, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            true,
        ));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('emoji'));

        parent::tearDown();
    }

    public function test_plain_text_is_html_escaped(): void
    {
        $html = PdfEmoji::toHtml('Ana & Marko <3');

        $this->assertSame('Ana &amp; Marko &lt;3', $html);
    }

    public function test_single_emoji_becomes_data_uri_image(): void
    {
        $html = PdfEmoji::toHtml('🌸');

        $this->assertStringContainsString('<img src="data:image/png;base64,', $html);
        $this->assertStringContainsString('class="pdf-emoji"', $html);
        $this->assertStringContainsString('height:1em', $html);
        $this->assertStringContainsString('width:1em', $html);
    }

    public function test_mixed_text_and_emoji_keeps_both(): void
    {
        $html = PdfEmoji::toHtml('Ana 🌸');

        $this->assertStringStartsWith('Ana ', $html);
        $this->assertStringContainsString('<img src="data:image/png;base64,', $html);
    }

    public function test_custom_size_is_applied_to_image(): void
    {
        $html = PdfEmoji::toHtml('🌸', '10px');

        $this->assertStringContainsString('height:10px', $html);
        $this->assertStringContainsString('width:10px', $html);
    }

    public function test_missing_emoji_asset_falls_back_to_escaped_character(): void
    {
        // U+1FAE8 (shaking face) is not in Twemoji 14.0.2
        $html = PdfEmoji::toHtml("\u{1FAE8}");

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString("\u{1FAE8}", $html);
    }

    public function test_empty_string_returns_empty_string(): void
    {
        $this->assertSame('', PdfEmoji::toHtml(''));
    }
}

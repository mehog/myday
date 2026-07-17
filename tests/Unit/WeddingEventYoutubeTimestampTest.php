<?php

namespace Tests\Unit;

use App\Models\WeddingEvent;
use Tests\TestCase;

class WeddingEventYoutubeTimestampTest extends TestCase
{
    public function test_parses_t_query_param_in_seconds(): void
    {
        $event = new WeddingEvent([
            'music_url' => 'https://www.youtube.com/watch?v=2Vv-BfVoq4g&t=18s',
        ]);

        $this->assertSame('2Vv-BfVoq4g', $event->youtube_video_id);
        $this->assertSame(18, $event->youtube_start_seconds);
        $this->assertStringContainsString('start=18', $event->youtube_embed_url);
    }

    public function test_parses_t_query_param_without_suffix(): void
    {
        $event = new WeddingEvent([
            'music_url' => 'https://www.youtube.com/watch?v=2Vv-BfVoq4g&t=18',
        ]);

        $this->assertSame(18, $event->youtube_start_seconds);
    }

    public function test_parses_compound_t_query_param(): void
    {
        $event = new WeddingEvent([
            'music_url' => 'https://www.youtube.com/watch?v=2Vv-BfVoq4g&t=1m18s',
        ]);

        $this->assertSame(78, $event->youtube_start_seconds);
    }

    public function test_ignores_urls_without_t_param(): void
    {
        $event = new WeddingEvent([
            'music_url' => 'https://www.youtube.com/watch?v=2Vv-BfVoq4g',
        ]);

        $this->assertNull($event->youtube_start_seconds);
        $this->assertStringNotContainsString('start=', $event->youtube_embed_url);
    }
}

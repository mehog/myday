<?php

namespace App\Jobs;

use App\LinkType;
use App\Models\LinkVisit;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class RecordLinkVisit
{
    use Queueable;

    private const DEDUPE_MINUTES = 30;

    public function __construct(
        public int $weddingEventId,
        public ?int $guestId,
        public LinkType $linkType,
        public ?string $ip,
        public ?string $userAgent,
        public ?string $referer,
    ) {}

    public function handle(): void
    {
        if (! Cache::add($this->dedupeKey(), true, now()->addMinutes(self::DEDUPE_MINUTES))) {
            return;
        }

        $agent = new Agent;

        if ($this->userAgent) {
            $agent->setUserAgent($this->userAgent);
        }

        if ($agent->isRobot()) {
            return;
        }

        $deviceType = match (true) {
            $agent->isTablet() => 'tablet',
            $agent->isMobile() => 'mobile',
            default => 'desktop',
        };

        LinkVisit::query()->create([
            'wedding_event_id' => $this->weddingEventId,
            'guest_id' => $this->guestId,
            'link_type' => $this->linkType,
            'ip_hash' => $this->ip ? hash('sha256', $this->ip) : null,
            'user_agent' => $this->userAgent,
            'referer' => $this->referer ? mb_substr($this->referer, 0, 512) : null,
            'device_type' => $deviceType,
            'browser' => $agent->browser() ?: null,
            'os' => $agent->platform() ?: null,
            'visited_at' => now(),
        ]);
    }

    private function dedupeKey(): string
    {
        $visitor = $this->guestId ?? ($this->ip ? hash('sha256', $this->ip) : 'unknown');

        return "link_visit:{$this->weddingEventId}:{$visitor}";
    }
}

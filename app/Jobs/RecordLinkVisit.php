<?php

namespace App\Jobs;

use App\LinkType;
use App\Models\LinkVisit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Jenssegers\Agent\Agent;

class RecordLinkVisit implements ShouldQueue
{
    use Queueable;

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
            'browser' => $agent->browser(),
            'os' => $agent->platform(),
            'visited_at' => now(),
        ]);
    }
}

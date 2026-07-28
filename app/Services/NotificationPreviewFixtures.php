<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use RuntimeException;

final class NotificationPreviewFixtures
{
    public function __construct(
        public WeddingEvent $wedding,
        public User $user,
        public Guest $guest,
    ) {}

    /**
     * @param  array<string, int|null>  $ids
     */
    public static function resolve(array $ids = []): self
    {
        $wedding = isset($ids['wedding_id'])
            ? WeddingEvent::query()->with('user')->find($ids['wedding_id'])
            : WeddingEvent::query()->with('user')->latest()->first();

        if ($wedding === null) {
            throw new RuntimeException('No wedding event found. Create one or pass --wedding-id=');
        }

        $guest = isset($ids['guest_id'])
            ? Guest::query()->with('weddingEvent.user')->find($ids['guest_id'])
            : $wedding->guests()->with('weddingEvent.user')->latest()->first();

        if ($guest === null) {
            throw new RuntimeException('No guest found. Create one or pass --guest-id=');
        }

        $user = isset($ids['user_id'])
            ? User::query()->with('weddingEvent')->find($ids['user_id'])
            : ($wedding->user ?? User::query()->with('weddingEvent')->latest()->first());

        if ($user === null) {
            throw new RuntimeException('No user found. Create one or pass --user-id=');
        }

        if ($user->weddingEvent === null) {
            $user->setRelation('weddingEvent', $wedding);
        }

        if ($guest->weddingEvent === null) {
            $guest->setRelation('weddingEvent', $wedding);
        }

        return new self($wedding, $user, $guest);
    }

    public function applyLocale(?string $locale): void
    {
        if ($locale === null) {
            return;
        }

        $this->user->locale = $locale;

        if ($this->wedding->user !== null) {
            $this->wedding->user->locale = $locale;
        }

        $this->guest->setRelation('weddingEvent', $this->wedding);
    }
}

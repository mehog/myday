<?php

namespace App\Services;

use App\Models\WeddingEvent;
use App\Models\WeddingMenuOption;
use App\PlatformMenu;

class EnsureWeddingMenuOptions
{
    public function handle(WeddingEvent $event): void
    {
        foreach (PlatformMenu::cases() as $platformMenu) {
            WeddingMenuOption::query()->firstOrCreate(
                [
                    'wedding_event_id' => $event->id,
                    'platform_key' => $platformMenu->value,
                ],
                [
                    'label' => null,
                    'is_visible' => true,
                    'sort_order' => $platformMenu->defaultSortOrder(),
                ],
            );
        }
    }
}

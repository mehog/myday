<?php

namespace Database\Seeders;

use App\LinkMode;
use App\Models\WeddingEvent;
use Illuminate\Database\Seeder;

class SetPersonalTokenLinkModeSeeder extends Seeder
{
    public function run(): void
    {
        WeddingEvent::query()->update([
            'link_mode' => LinkMode::TokenOnly,
        ]);
    }
}

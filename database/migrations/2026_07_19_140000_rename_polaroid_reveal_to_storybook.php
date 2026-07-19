<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('wedding_events')
            ->where('reveal_animation', 'polaroid')
            ->update(['reveal_animation' => 'storybook']);
    }

    public function down(): void
    {
        DB::table('wedding_events')
            ->where('reveal_animation', 'storybook')
            ->update(['reveal_animation' => 'polaroid']);
    }
};

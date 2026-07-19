<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('wedding_events')
            ->where('reveal_animation', 'starlit-constellation')
            ->update(['reveal_animation' => 'sunrise-bloom']);
    }

    public function down(): void
    {
        DB::table('wedding_events')
            ->where('reveal_animation', 'sunrise-bloom')
            ->update(['reveal_animation' => 'starlit-constellation']);
    }
};

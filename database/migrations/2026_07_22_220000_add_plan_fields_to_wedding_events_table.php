<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->string('plan_tier')->nullable()->after('is_demo');
            $table->unsignedInteger('guest_limit')->nullable()->after('plan_tier');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->dropColumn(['plan_tier', 'guest_limit']);
        });
    }
};

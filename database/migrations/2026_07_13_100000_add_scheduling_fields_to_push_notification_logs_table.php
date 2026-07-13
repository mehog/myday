<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_notification_logs', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('sent_at');
            $table->json('guest_ids')->nullable()->after('sent_to_count');
        });
    }

    public function down(): void
    {
        Schema::table('push_notification_logs', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'guest_ids']);
        });
    }
};

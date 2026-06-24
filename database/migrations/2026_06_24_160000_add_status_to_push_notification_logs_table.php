<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_notification_logs', function (Blueprint $table) {
            $table->string('status')->default('queued')->after('sent_to_count');
            $table->text('failed_reason')->nullable()->after('status');
            $table->timestamp('sent_at')->nullable()->after('failed_reason');
        });
    }

    public function down(): void
    {
        Schema::table('push_notification_logs', function (Blueprint $table) {
            $table->dropColumn(['status', 'failed_reason', 'sent_at']);
        });
    }
};

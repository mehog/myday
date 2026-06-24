<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_messages', function (Blueprint $table) {
            $table->timestamp('seen_at')->nullable()->after('file_paths');
            $table->index(['wedding_event_id', 'seen_at']);
        });
    }

    public function down(): void
    {
        Schema::table('guest_messages', function (Blueprint $table) {
            $table->dropIndex(['wedding_event_id', 'seen_at']);
            $table->dropColumn('seen_at');
        });
    }
};

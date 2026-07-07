<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_messages', function (Blueprint $table) {
            $table->string('ip_hash', 64)->nullable()->after('sender_name');
            $table->text('user_agent')->nullable()->after('ip_hash');
            $table->string('device_type', 16)->nullable()->after('user_agent');
            $table->string('browser', 64)->nullable()->after('device_type');
            $table->string('os', 64)->nullable()->after('browser');
        });
    }

    public function down(): void
    {
        Schema::table('guest_messages', function (Blueprint $table) {
            $table->dropColumn([
                'ip_hash',
                'user_agent',
                'device_type',
                'browser',
                'os',
            ]);
        });
    }
};

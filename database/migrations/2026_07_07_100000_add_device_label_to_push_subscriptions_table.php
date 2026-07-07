<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('webpush.database_connection'))->table(config('webpush.table_name'), function (Blueprint $table) {
            $table->string('device_label')->nullable()->after('content_encoding');
        });
    }

    public function down(): void
    {
        Schema::connection(config('webpush.database_connection'))->table(config('webpush.table_name'), function (Blueprint $table) {
            $table->dropColumn('device_label');
        });
    }
};

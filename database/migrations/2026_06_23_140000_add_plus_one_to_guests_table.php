<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->boolean('plus_one_allowed')->default(false)->after('phone');
            $table->string('plus_one_name')->nullable()->after('plus_one_allowed');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['plus_one_allowed', 'plus_one_name']);
        });
    }
};

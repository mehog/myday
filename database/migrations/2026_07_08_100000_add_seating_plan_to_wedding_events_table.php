<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->json('seating_plan')->nullable()->after('motto');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->dropColumn('seating_plan');
        });
    }
};

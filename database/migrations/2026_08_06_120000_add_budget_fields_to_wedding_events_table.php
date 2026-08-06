<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->string('budget_currency', 3)->nullable()->after('seating_plan');
            $table->string('budget_guest_mode')->default('confirmed')->after('budget_currency');
            $table->unsignedInteger('budget_guest_count')->nullable()->after('budget_guest_mode');
            $table->decimal('budget_target', 12, 2)->nullable()->after('budget_guest_count');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->dropColumn([
                'budget_currency',
                'budget_guest_mode',
                'budget_guest_count',
                'budget_target',
            ]);
        });
    }
};

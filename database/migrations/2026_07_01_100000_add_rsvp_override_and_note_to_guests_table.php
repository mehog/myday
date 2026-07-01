<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->boolean('rsvp_manual_override')->default(false)->after('rsvp_responded_at');
            $table->text('rsvp_note')->nullable()->after('rsvp_manual_override');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['rsvp_manual_override', 'rsvp_note']);
        });
    }
};

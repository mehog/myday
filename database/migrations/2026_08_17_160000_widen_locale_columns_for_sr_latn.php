<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->string('invitation_locale', 16)->default('en')->change();
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->string('invitation_locale', 16)->nullable()->change();
        });

        Schema::table('discount_email_recipients', function (Blueprint $table) {
            $table->string('locale', 16)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->string('invitation_locale', 5)->default('en')->change();
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->string('invitation_locale', 5)->nullable()->change();
        });

        Schema::table('discount_email_recipients', function (Blueprint $table) {
            $table->string('locale', 5)->nullable()->change();
        });
    }
};

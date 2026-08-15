<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_email_campaigns', function (Blueprint $table) {
            $table->foreignId('discount_code_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('discount_email_campaigns', function (Blueprint $table) {
            $table->foreignId('discount_code_id')->nullable(false)->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_email_campaigns', function (Blueprint $table) {
            $table->foreignId('discount_email_template_id')
                ->nullable()
                ->after('discount_code_id')
                ->constrained('discount_email_templates')
                ->restrictOnDelete();

            $table->string('subject')->nullable()->change();
            $table->text('body')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('discount_email_campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_email_template_id');
        });
    }
};

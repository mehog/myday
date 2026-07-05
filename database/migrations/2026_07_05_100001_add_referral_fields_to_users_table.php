<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('referral_fee_percentage', 5, 2)->nullable()->after('locale');
            $table->string('paypal_email')->nullable()->after('referral_fee_percentage');
            $table->text('bank_account_info')->nullable()->after('paypal_email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'referral_fee_percentage',
                'paypal_email',
                'bank_account_info',
            ]);
        });
    }
};

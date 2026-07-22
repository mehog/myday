<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dodo_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wedding_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plan_tier');
            $table->string('pricing_region');
            $table->string('currency', 3);
            $table->unsignedInteger('amount');
            $table->string('status')->default('pending');
            $table->string('dodo_product_id');
            $table->string('dodo_payment_id')->nullable()->unique();
            $table->string('dodo_checkout_session_id')->nullable()->index();
            $table->string('dodo_customer_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['wedding_event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dodo_payments');
    }
};

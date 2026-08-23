<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_event_id')->constrained()->cascadeOnDelete();
            $table->string('system_key')->nullable();
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->string('period');
            $table->string('priority')->default('normal');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['wedding_event_id', 'system_key']);
            $table->index(['wedding_event_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_tasks');
    }
};

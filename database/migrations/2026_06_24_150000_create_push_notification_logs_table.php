<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_event_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('body', 500);
            $table->string('recipient_type');
            $table->unsignedInteger('sent_to_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notification_logs');
    }
};

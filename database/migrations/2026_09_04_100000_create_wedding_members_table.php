<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('partner');
            $table->timestamps();

            $table->unique(['wedding_event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_members');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('enquiries');
    }

    public function down(): void
    {
        // Intentionally empty — enquiry order flow was removed in favor of onboarding.
    }
};

<?php

use App\PlanTier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('wedding_events')
            ->whereNull('plan_tier')
            ->update([
                'plan_tier' => PlanTier::Free->value,
                'guest_limit' => 50,
                'is_active' => true,
            ]);

        DB::table('wedding_events')
            ->where('plan_tier', PlanTier::Plus->value)
            ->where('guest_limit', 200)
            ->update(['guest_limit' => 250]);

        DB::table('wedding_events')
            ->where('plan_tier', PlanTier::Premium->value)
            ->where('guest_limit', 300)
            ->update(['guest_limit' => null]);
    }

    public function down(): void
    {
        DB::table('wedding_events')
            ->where('plan_tier', PlanTier::Free->value)
            ->update([
                'plan_tier' => null,
                'guest_limit' => null,
                'is_active' => false,
            ]);

        DB::table('wedding_events')
            ->where('plan_tier', PlanTier::Plus->value)
            ->where('guest_limit', 250)
            ->update(['guest_limit' => 200]);

        DB::table('wedding_events')
            ->where('plan_tier', PlanTier::Premium->value)
            ->whereNull('guest_limit')
            ->update(['guest_limit' => 300]);
    }
};

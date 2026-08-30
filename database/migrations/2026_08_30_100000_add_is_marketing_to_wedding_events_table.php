<?php

use App\Models\WeddingEvent;
use App\Services\WeddingScheduledNotificationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const MARKETING_SLUGS = [
        'jasmina-djordje',
        'lukas-sophie',
        'oliver-emily',
        'ivan-lucija',
        'nikola-milica',
    ];

    /**
     * @var list<string>
     */
    private const MARKETING_OWNER_EMAILS = [
        'jasmin-djordje@nasdan.app',
        'marketing-de@nasdan.app',
        'marketing-en@nasdan.app',
        'marketing-hr@nasdan.app',
        'marketing-sr@nasdan.app',
    ];

    public function up(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->boolean('is_marketing')->default(false)->after('is_demo');
        });

        $events = WeddingEvent::query()
            ->where(function ($query): void {
                $query->whereIn('slug', self::MARKETING_SLUGS)
                    ->orWhereHas('user', fn ($q) => $q->whereIn('email', self::MARKETING_OWNER_EMAILS))
                    ->orWhereHas('user', fn ($q) => $q->where('email', 'like', 'marketing-%@nasdan.%'))
                    ->orWhereHas('guests', fn ($q) => $q->where('email', 'like', 'marketing-%guest%@%'));
            })
            ->get();

        $scheduler = app(WeddingScheduledNotificationService::class);

        foreach ($events as $event) {
            $event->forceFill(['is_marketing' => true])->save();
            $scheduler->syncEvent($event);
            $scheduler->syncCoupleOnboarding($event);
            $scheduler->syncAdminAlertsForEvent($event);
        }
    }

    public function down(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->dropColumn('is_marketing');
        });
    }
};

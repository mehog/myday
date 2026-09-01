<?php

namespace Tests\Feature;

use App\Filament\Resources\WeddingEvents\Pages\ViewWeddingEvent;
use App\Filament\Resources\WeddingEvents\RelationManagers\BudgetItemsRelationManager;
use App\Filament\Resources\WeddingEvents\RelationManagers\DodoPaymentsRelationManager;
use App\Filament\Resources\WeddingEvents\RelationManagers\PushNotificationLogsRelationManager;
use App\Filament\Resources\WeddingEvents\RelationManagers\TasksRelationManager;
use App\Filament\Resources\WeddingEvents\WeddingEventResource;
use App\Models\User;
use App\Models\WeddingBudgetItem;
use App\Models\WeddingEvent;
use App\Models\WeddingTask;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class AdminWeddingEventRelationsTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_wedding_event_resource_includes_new_relation_managers(): void
    {
        $relations = WeddingEventResource::getRelations();

        $this->assertContains(BudgetItemsRelationManager::class, $relations);
        $this->assertContains(TasksRelationManager::class, $relations);
        $this->assertContains(PushNotificationLogsRelationManager::class, $relations);
        $this->assertContains(DodoPaymentsRelationManager::class, $relations);
    }

    public function test_wedding_event_resource_eager_loads_relation_counts(): void
    {
        $event = WeddingEvent::factory()->create();
        WeddingBudgetItem::factory()->count(2)->for($event)->create();
        WeddingTask::query()
            ->where('wedding_event_id', $event->id)
            ->limit(1)
            ->update(['completed_at' => now()]);

        $record = WeddingEventResource::getEloquentQuery()
            ->whereKey($event->id)
            ->first();

        $this->assertNotNull($record);
        $this->assertSame(2, $record->budget_items_count);
        $this->assertGreaterThan(0, $record->tasks_count);
        $this->assertSame(1, $record->completed_tasks_count);
    }

    public function test_relation_badge_is_null_when_empty_and_shows_count_when_present(): void
    {
        $emptyEvent = WeddingEvent::factory()->create();

        $this->assertNull(BudgetItemsRelationManager::getBadge($emptyEvent, ViewWeddingEvent::class));

        WeddingBudgetItem::factory()->count(3)->for($emptyEvent)->create();
        $emptyEvent->refresh();

        $this->assertSame('3', BudgetItemsRelationManager::getBadge($emptyEvent, ViewWeddingEvent::class));
        $this->assertSame('primary', BudgetItemsRelationManager::getBadgeColor($emptyEvent, ViewWeddingEvent::class));
    }

    public function test_relation_badge_uses_eager_loaded_count(): void
    {
        $event = WeddingEvent::factory()->create();
        WeddingBudgetItem::factory()->count(2)->for($event)->create();

        $record = WeddingEventResource::getEloquentQuery()
            ->whereKey($event->id)
            ->first();

        $this->assertSame('2', BudgetItemsRelationManager::getBadge($record, ViewWeddingEvent::class));
    }

    public function test_wedding_event_exposes_seating_summary_helpers(): void
    {
        $event = WeddingEvent::factory()->create([
            'seating_plan' => [
                'tables' => [
                    [
                        'name' => 'Table 1',
                        'seats' => ['guest-1', 'guest-2', 'bride', null],
                    ],
                    [
                        'name' => 'Table 2',
                        'seats' => [],
                    ],
                ],
            ],
        ]);

        $this->assertSame(2, $event->seatingTablesCount());
        $this->assertSame(2, $event->assignedSeatingCount());
    }

    public function test_admin_can_access_wedding_view_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = WeddingEvent::factory()->create();

        $this->actingAs($admin)
            ->get(WeddingEventResource::getUrl('view', ['record' => $event], panel: 'admin'))
            ->assertOk();
    }
}

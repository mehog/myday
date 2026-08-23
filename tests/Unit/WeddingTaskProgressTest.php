<?php

namespace Tests\Unit;

use App\BudgetCalculationType;
use App\BudgetCategory;
use App\Models\Guest;
use App\Models\ScheduleItem;
use App\Models\User;
use App\Models\WeddingBudgetItem;
use App\Models\WeddingEvent;
use App\Models\WeddingTask;
use App\RsvpStatus;
use App\Services\EnsureWeddingTasks;
use App\Services\WeddingTaskProgress;
use App\Support\WeddingTaskCatalog;
use Illuminate\Support\Carbon;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class WeddingTaskProgressTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_creating_a_wedding_seeds_catalog_tasks_once(): void
    {
        $wedding = WeddingEvent::factory()->create();

        $this->assertSame(count(WeddingTaskCatalog::keys()), $wedding->tasks()->count());
        $this->assertSame(count(WeddingTaskCatalog::keys()), $wedding->tasks()->whereNotNull('system_key')->count());

        app(EnsureWeddingTasks::class)->handle($wedding);

        $this->assertSame(count(WeddingTaskCatalog::keys()), $wedding->tasks()->count());
    }

    public function test_due_dates_are_offset_from_the_wedding_date(): void
    {
        Carbon::setTestNow('2026-01-15 10:00:00');

        $wedding = WeddingEvent::factory()->create([
            'wedding_date' => Carbon::parse('2026-12-01')->startOfDay(),
        ]);

        $definition = WeddingTaskCatalog::definition('send_invitations');
        $this->assertNotNull($definition);

        $task = $wedding->tasks()->where('system_key', 'send_invitations')->first();

        $this->assertNotNull($task);
        $this->assertSame(
            Carbon::parse('2026-12-01')->subDays($definition['due_offset_days'])->toDateString(),
            $task->due_date?->toDateString(),
        );
    }

    public function test_changing_the_wedding_date_moves_incomplete_system_due_dates(): void
    {
        Carbon::setTestNow('2026-01-15 10:00:00');

        $wedding = WeddingEvent::factory()->create([
            'wedding_date' => Carbon::parse('2026-12-01')->startOfDay(),
        ]);

        $task = $wedding->tasks()->where('system_key', 'send_invitations')->firstOrFail();
        $originalDue = $task->due_date?->toDateString();

        $completed = $wedding->tasks()->where('system_key', 'book_venue')->firstOrFail();
        $completedDue = $completed->due_date?->toDateString();
        $completed->update(['completed_at' => now()]);

        $wedding->update([
            'wedding_date' => Carbon::parse('2026-11-01')->startOfDay(),
        ]);

        $this->assertNotSame($originalDue, $task->fresh()->due_date?->toDateString());
        $this->assertSame(
            Carbon::parse('2026-11-01')->subDays(WeddingTaskCatalog::definition('send_invitations')['due_offset_days'])->toDateString(),
            $task->fresh()->due_date?->toDateString(),
        );
        $this->assertSame($completedDue, $completed->fresh()->due_date?->toDateString());
    }

    public function test_invitation_and_rsvp_progress(): void
    {
        $wedding = WeddingEvent::factory()->create();
        Guest::factory()->for($wedding)->count(2)->create();
        Guest::factory()->for($wedding)->create([
            'invite_sent_at' => now(),
            'rsvp_status' => RsvpStatus::Yes,
        ]);

        $progress = app(WeddingTaskProgress::class);

        $invites = $progress->for($wedding, $this->systemTask($wedding, 'send_invitations'));
        $this->assertSame(1, $invites['current']);
        $this->assertSame(3, $invites['target']);
        $this->assertSame(__('checklist.progress.send_invitations', ['current' => 1, 'total' => 3]), $invites['label']);

        $rsvp = $progress->for($wedding, $this->systemTask($wedding, 'track_rsvp'));
        $this->assertSame(1, $rsvp['current']);
        $this->assertSame(3, $rsvp['target']);
    }

    public function test_seating_and_menu_progress(): void
    {
        $wedding = WeddingEvent::factory()->create();
        $guest = Guest::factory()->for($wedding)->create([
            'rsvp_status' => RsvpStatus::Yes,
            'menu_option_id' => $wedding->menuOptions()->first()?->id,
        ]);
        Guest::factory()->for($wedding)->create([
            'rsvp_status' => RsvpStatus::Yes,
        ]);

        $wedding->update([
            'seating_plan' => [
                'tables' => [
                    ['seats' => [$guest->id, 'bride', null]],
                ],
            ],
        ]);

        $progress = app(WeddingTaskProgress::class);

        $seating = $progress->for($wedding->fresh(), $this->systemTask($wedding, 'finish_seating'));
        $this->assertSame(1, $seating['current']);
        $this->assertSame(2, $seating['target']);

        $menu = $progress->for($wedding, $this->systemTask($wedding, 'confirm_menu'));
        $this->assertSame(1, $menu['current']);
        $this->assertSame(2, $menu['target']);
    }

    public function test_budget_progress_uses_target_when_set(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'budget_target' => '1000.00',
            'budget_currency' => 'EUR',
        ]);

        WeddingBudgetItem::factory()->for($wedding)->paid()->create([
            'name' => 'Band',
            'category' => BudgetCategory::BendIGlazba,
            'calculation_type' => BudgetCalculationType::Fixed,
            'amount' => 250,
        ]);

        $progress = app(WeddingTaskProgress::class)->for($wedding->fresh(), $this->systemTask($wedding, 'review_budget'));

        $this->assertSame(25000, $progress['current']);
        $this->assertSame(100000, $progress['target']);
        $this->assertStringContainsString('250,00 EUR', $progress['label']);
        $this->assertStringContainsString('1.000,00 EUR', $progress['label']);
    }

    public function test_schedule_location_and_guest_presence_progress(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->for($user)->create([
            'location_name' => null,
            'location_address' => null,
        ]);
        $wedding->locations()->delete();

        $progress = app(WeddingTaskProgress::class);

        $location = $progress->for($wedding->fresh(), $this->systemTask($wedding, 'set_location'));
        $this->assertSame(0, $location['current']);

        $schedule = $progress->for($wedding, $this->systemTask($wedding, 'set_schedule'));
        $this->assertSame(0, $schedule['current']);

        ScheduleItem::query()->create([
            'wedding_event_id' => $wedding->id,
            'time' => '16:00',
            'title' => 'Ceremony',
            'sort_order' => 0,
        ]);

        $this->assertSame(1, $progress->for($wedding, $this->systemTask($wedding, 'set_schedule'))['current']);

        $guests = $progress->for($wedding, $this->systemTask($wedding, 'add_guests'));
        $this->assertSame(0, $guests['current']);

        Guest::factory()->for($wedding)->create();
        $this->assertSame(1, $progress->for($wedding, $this->systemTask($wedding, 'add_guests'))['current']);
    }

    public function test_sync_command_backfills_missing_system_tasks(): void
    {
        $wedding = WeddingEvent::factory()->create();
        WeddingTask::query()->where('wedding_event_id', $wedding->id)->delete();

        $this->artisan('wedding-tasks:sync')
            ->assertSuccessful();

        $this->assertSame(count(WeddingTaskCatalog::keys()), $wedding->tasks()->count());
    }

    protected function systemTask(WeddingEvent $wedding, string $key): WeddingTask
    {
        return $wedding->tasks()->where('system_key', $key)->firstOrFail();
    }
}

<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\Checklist;
use App\Livewire\Dashboard\Home as DashboardHome;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Support\WeddingTaskCatalog;
use App\WeddingTaskPeriod;
use App\WeddingTaskPriority;
use Livewire\Livewire;
use Tests\TestCase;

class WeddingChecklistTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_couple_can_view_checklist_with_seeded_tasks(): void
    {
        [$user] = $this->coupleWithWedding();

        $this->actingAs($user)
            ->get(route('dashboard.checklist'))
            ->assertOk()
            ->assertSee(__('checklist.title'))
            ->assertSee(__('checklist.tasks.send_invitations.title'))
            ->assertSee(__('dashboard.nav.checklist'));

        Livewire::actingAs($user)
            ->test(Checklist::class)
            ->assertSee(__('checklist.summary_label'))
            ->assertSee(__('checklist.tasks.add_guests.title'));
    }

    public function test_couple_can_toggle_a_system_task(): void
    {
        [$user, $wedding] = $this->coupleWithWedding();
        $task = $wedding->tasks()->where('system_key', 'book_venue')->firstOrFail();

        Livewire::actingAs($user)
            ->test(Checklist::class)
            ->call('toggle', $task->id)
            ->assertOk();

        $this->assertNotNull($task->fresh()->completed_at);

        Livewire::actingAs($user)
            ->test(Checklist::class)
            ->call('toggle', $task->id)
            ->assertOk();

        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_couple_can_create_edit_and_delete_a_custom_task(): void
    {
        [$user, $wedding] = $this->coupleWithWedding();

        Livewire::actingAs($user)
            ->test(Checklist::class)
            ->call('openCreate')
            ->set('title', 'Call florist')
            ->set('due_date', now()->addWeek()->toDateString())
            ->set('notes', 'Ask about peonies')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Call florist');

        $task = $wedding->tasks()->whereNull('system_key')->where('title', 'Call florist')->first();
        $this->assertNotNull($task);
        $this->assertSame(WeddingTaskPeriod::Custom, $task->period);
        $this->assertSame(WeddingTaskPriority::Normal, $task->priority);

        Livewire::actingAs($user)
            ->test(Checklist::class)
            ->call('openEdit', $task->id)
            ->set('title', 'Call the florist again')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Call the florist again');

        Livewire::actingAs($user)
            ->test(Checklist::class)
            ->call('delete', $task->id)
            ->assertOk()
            ->assertDontSee('Call the florist again');

        $this->assertNull($task->fresh());
    }

    public function test_system_tasks_cannot_be_edited_or_deleted(): void
    {
        [$user, $wedding] = $this->coupleWithWedding();
        $task = $wedding->tasks()->where('system_key', 'send_invitations')->firstOrFail();

        Livewire::actingAs($user)
            ->test(Checklist::class)
            ->call('openEdit', $task->id)
            ->assertForbidden();

        Livewire::actingAs($user)
            ->test(Checklist::class)
            ->call('delete', $task->id)
            ->assertForbidden();
    }

    public function test_tabs_filter_predefined_custom_and_completed_tasks(): void
    {
        [$user, $wedding] = $this->coupleWithWedding();

        $wedding->tasks()->create([
            'system_key' => null,
            'title' => 'Pick up the dress',
            'period' => WeddingTaskPeriod::Custom,
            'priority' => WeddingTaskPriority::Normal,
            'sort_order' => 999,
        ]);

        $wedding->tasks()->where('system_key', 'book_venue')->update(['completed_at' => now()]);

        Livewire::actingAs($user)
            ->test(Checklist::class)
            ->set('tab', 'mine')
            ->assertSee('Pick up the dress')
            ->assertDontSee(__('checklist.tasks.send_invitations.title'))
            ->set('tab', 'predefined')
            ->assertSee(__('checklist.tasks.send_invitations.title'))
            ->assertDontSee('Pick up the dress')
            ->set('tab', 'completed')
            ->assertSee(__('checklist.tasks.book_venue.title'))
            ->assertDontSee('Pick up the dress');
    }

    public function test_archived_wedding_checklist_is_read_only(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $wedding = WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'wedding_date' => now()->subDay(),
        ]);
        $task = $wedding->tasks()->where('system_key', 'book_venue')->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard.checklist'))
            ->assertOk()
            ->assertSee(__('app.wedding_archived_readonly'));

        Livewire::actingAs($user)
            ->test(Checklist::class)
            ->call('toggle', $task->id)
            ->assertForbidden();
    }

    public function test_home_overview_shows_checklist_widget(): void
    {
        [$user] = $this->coupleWithWedding();

        Livewire::actingAs($user)
            ->test(DashboardHome::class)
            ->assertSee(__('checklist.summary_label'))
            ->assertSee(__('checklist.next_heading'))
            ->assertSee(__('checklist.view_all'));
    }

    public function test_catalog_has_a_full_suggested_list(): void
    {
        $this->assertGreaterThanOrEqual(25, count(WeddingTaskCatalog::all()));
    }

    /**
     * @return array{0: User, 1: WeddingEvent}
     */
    protected function coupleWithWedding(): array
    {
        $user = User::factory()->create(['is_admin' => false]);
        $wedding = WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'wedding_date' => now()->addMonths(4),
        ]);

        return [$user, $wedding];
    }
}

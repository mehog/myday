<?php

namespace Tests\Feature;

use App\Filament\App\Pages\AppDashboard;
use App\Filament\App\Resources\GuestMessagesResource;
use App\Filament\App\Widgets\RecentGuestMessagesWidget;
use App\Filament\App\Widgets\VisitChartWidget;
use App\Filament\App\Widgets\VisitStatsWidget;
use App\Filament\App\Widgets\WeddingMemoriesWidget;
use App\Filament\App\Widgets\WeddingOverviewWidget;
use App\GuestMessageType;
use App\Models\Guest;
use App\Models\GuestChild;
use App\Models\GuestMessage;
use App\Models\ScheduleItem;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class WeddingMemoriesDashboardTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_pre_wedding_dashboard_uses_planning_widgets(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'wedding_date' => now()->addMonth()->setTime(16, 0),
            'is_active' => true,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        $dashboard = Livewire::test(AppDashboard::class)
            ->assertSuccessful()
            ->assertSee(__('app.dashboard_title'));

        $this->assertSame([
            RecentGuestMessagesWidget::class,
            WeddingOverviewWidget::class,
            VisitStatsWidget::class,
            VisitChartWidget::class,
        ], $dashboard->instance()->getWidgets());
        $this->assertSame(2, $dashboard->instance()->getColumns());
    }

    public function test_archived_dashboard_shows_memories_recap(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create([
            'groom_name' => 'Marko',
            'bride_name' => 'Ana',
            'wedding_date' => now()->subDays(3)->setTime(16, 0),
            'is_active' => true,
        ]);

        $guest = Guest::factory()->for($event)->create([
            'name' => 'Guest Wish',
            'rsvp_status' => RsvpStatus::Yes,
            'rsvp_responded_at' => now()->subWeek(),
            'plus_one_name' => 'Companion',
        ]);
        GuestChild::query()->create([
            'guest_id' => $guest->id,
            'name' => 'Kid',
            'sort_order' => 0,
        ]);
        Guest::factory()->for($event)->create([
            'name' => 'Pending Guest',
            'rsvp_status' => null,
        ]);

        ScheduleItem::query()->create([
            'wedding_event_id' => $event->id,
            'time' => '16:00:00',
            'title' => 'Ceremony',
            'description' => 'At the chapel',
            'sort_order' => 1,
        ]);

        GuestMessage::query()->create([
            'wedding_event_id' => $event->id,
            'guest_id' => $guest->id,
            'sender_name' => $guest->name,
            'type' => GuestMessageType::Text,
            'content' => 'Congratulations forever!',
        ]);

        $photo = UploadedFile::fake()->image('memory.jpg');
        $path = $photo->store('guest-messages/photos', 'media');

        GuestMessage::query()->create([
            'wedding_event_id' => $event->id,
            'guest_id' => $guest->id,
            'sender_name' => $guest->name,
            'type' => GuestMessageType::Photo,
            'file_paths' => [$path],
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        $dashboard = Livewire::test(AppDashboard::class)
            ->assertSuccessful()
            ->assertSee(__('app.memories_dashboard_title'))
            ->assertSee(__('app.wedding_archived_badge'))
            ->assertSee(__('app.view_invitation'));

        $this->assertSame([
            WeddingMemoriesWidget::class,
        ], $dashboard->instance()->getWidgets());
        $this->assertSame(1, $dashboard->instance()->getColumns());
        $this->assertNull($dashboard->instance()->getMaxContentWidth());

        Livewire::test(WeddingMemoriesWidget::class)
            ->assertSuccessful()
            ->assertSee(__('app.memories_heading'))
            ->assertSee('Marko & Ana')
            ->assertSee('Ceremony')
            ->assertSee('Congratulations forever!')
            ->assertSee(__('app.memories_stat_invited'))
            ->assertSee('2')
            ->assertSee(__('app.guest_messages_view_all_photos'))
            ->assertSee(GuestMessagesResource::getUrl('photos'), false)
            ->assertSee(route('guest-messages.photos.download'), false)
            ->assertSeeHtml('grid grid-cols-2 gap-2 sm:gap-3')
            ->assertSeeHtml('grid grid-cols-2 gap-3 xl:grid-cols-4');
    }

    public function test_archived_dashboard_handles_empty_memory_state(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'is_active' => true,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(WeddingMemoriesWidget::class)
            ->assertSuccessful()
            ->assertSee(__('app.memories_schedule_empty'))
            ->assertSee(__('app.memories_wishes_empty'))
            ->assertSee(__('app.memories_audio_empty'))
            ->assertSee(__('app.memories_photos_empty'));
    }

    public function test_memories_widget_is_hidden_before_wedding_ends(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'wedding_date' => now()->addWeek()->setTime(16, 0),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $this->assertFalse(WeddingMemoriesWidget::canView());
    }
}

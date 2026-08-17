<?php

namespace Tests\Feature\Dashboard;

use App\GuestMessageType;
use App\Livewire\Dashboard\Profile as DashboardProfile;
use App\Livewire\Dashboard\Wedding as DashboardWedding;
use App\Livewire\Onboarding\VerifyEmailNotice;
use App\Livewire\Onboarding\WeddingOnboarding;
use App\Livewire\UpgradeRequiredModal;
use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\ScheduleItem;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Support\DashboardNav;
use App\Support\MediaDisk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CoupleDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_cannot_access_couple_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_verified_couple_can_view_dashboard_home(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        WeddingEvent::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('NasDan');
    }

    public function test_dashboard_pages_are_reachable(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        WeddingEvent::factory()->create(['user_id' => $user->id]);

        $routes = [
            'dashboard',
            'dashboard.wedding',
            'dashboard.locations',
            'dashboard.menus',
            'dashboard.schedule',
            'dashboard.photos',
            'dashboard.guests',
            'dashboard.messages',
            'dashboard.budget',
            'dashboard.seating',
            'dashboard.pushes',
            'dashboard.pushes.create',
            'dashboard.pricing',
            'dashboard.referrals',
            'dashboard.profile',
        ];

        foreach ($routes as $name) {
            $this->actingAs($user)
                ->get(route($name))
                ->assertOk();
        }
    }

    public function test_unverified_couple_is_redirected_from_dashboard(): void
    {
        $user = User::factory()->unverified()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect();
    }

    public function test_wedding_form_can_upload_replace_and_remove_hero_image(): void
    {
        Storage::fake(MediaDisk::name());

        $user = User::factory()->create(['is_admin' => false]);
        $wedding = WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'wedding_date' => now()->addMonth(),
        ]);

        Livewire::actingAs($user)
            ->test(DashboardWedding::class)
            ->set('heroUpload', UploadedFile::fake()->image('hero.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $wedding->refresh();
        $this->assertNotNull($wedding->hero_image);
        $this->assertTrue(Storage::disk(MediaDisk::name())->exists($wedding->hero_image));
        $originalPath = $wedding->hero_image;

        Livewire::actingAs($user)
            ->test(DashboardWedding::class)
            ->set('heroUpload', UploadedFile::fake()->image('hero-2.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $wedding->refresh();
        $this->assertNotNull($wedding->hero_image);
        $this->assertNotSame($originalPath, $wedding->hero_image);

        Livewire::actingAs($user)
            ->test(DashboardWedding::class)
            ->call('clearHero')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($wedding->fresh()->hero_image);
    }

    public function test_locked_wedding_rejects_hero_mutation(): void
    {
        Storage::fake(MediaDisk::name());

        $user = User::factory()->create(['is_admin' => false]);
        WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(DashboardWedding::class)
            ->set('heroUpload', UploadedFile::fake()->image('hero.jpg'))
            ->call('save')
            ->assertForbidden();
    }

    public function test_profile_can_change_password_and_remove_push_device(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        WeddingEvent::factory()->create(['user_id' => $user->id]);

        $user->updatePushSubscription(
            endpoint: 'https://push.example.com/device-1',
            key: base64_encode(random_bytes(32)),
            token: base64_encode(random_bytes(16)),
        );
        $device = $user->pushSubscriptions()->first();
        $device->forceFill(['device_label' => 'Chrome on Mac'])->save();

        Livewire::actingAs($user)
            ->test(DashboardProfile::class)
            ->set('current_password', 'password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));

        Livewire::actingAs($user)
            ->test(DashboardProfile::class)
            ->call('removeDevice', $device->id)
            ->assertSet('flashMessage', __('app.push_devices_removed'));

        $this->assertDatabaseMissing('push_subscriptions', ['id' => $device->id]);
    }

    public function test_onboarding_and_upgrade_cta_land_on_v2_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        WeddingEvent::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(WeddingOnboarding::class)
            ->assertRedirect(DashboardNav::homeUrl());

        Livewire::actingAs($user)
            ->test(VerifyEmailNotice::class)
            ->assertRedirect(DashboardNav::homeUrl());

        Livewire::test(UpgradeRequiredModal::class)
            ->call('open')
            ->assertSee(DashboardNav::pricingUrl(), false);
    }

    public function test_archived_home_renders_schedule_time_and_message_content(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $wedding = WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'wedding_date' => now()->subDays(3)->setTime(16, 0),
            'is_active' => true,
        ]);

        $guest = Guest::factory()->create(['wedding_event_id' => $wedding->id]);

        ScheduleItem::query()->create([
            'wedding_event_id' => $wedding->id,
            'time' => '16:00:00',
            'title' => 'Ceremony',
            'description' => 'At the chapel',
            'sort_order' => 1,
        ]);

        GuestMessage::query()->create([
            'wedding_event_id' => $wedding->id,
            'guest_id' => $guest->id,
            'sender_name' => $guest->name,
            'type' => GuestMessageType::Text,
            'content' => 'Congratulations forever!',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ceremony')
            ->assertSee('16:00')
            ->assertSee('Congratulations forever!');
    }
}

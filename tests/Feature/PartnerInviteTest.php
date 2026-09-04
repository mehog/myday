<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\PlanningPartner;
use App\Livewire\PartnerInviteAccept;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Models\WeddingMember;
use App\Models\WeddingPartnerInvite;
use App\Notifications\PartnerInviteNotification;
use App\Services\WeddingPartnerInviteService;
use App\WeddingMemberRole;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class PartnerInviteTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_owner_can_send_partner_invite_email(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $wedding = WeddingEvent::factory()->create(['user_id' => $owner->id]);

        Livewire::actingAs($owner)
            ->test(PlanningPartner::class)
            ->set('partner_email', 'partner@example.com')
            ->call('sendPartnerInvite')
            ->assertHasNoErrors();

        $invite = WeddingPartnerInvite::query()->first();

        $this->assertNotNull($invite);
        $this->assertSame('partner@example.com', $invite->email);
        $this->assertSame($wedding->id, $invite->wedding_event_id);

        Notification::assertSentOnDemand(PartnerInviteNotification::class, function ($notification, $channels, $notifiable): bool {
            return ($notifiable->routes['mail'] ?? null) === 'partner@example.com';
        });
    }

    public function test_partner_can_accept_invite_via_registration(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $wedding = WeddingEvent::factory()->create(['user_id' => $owner->id]);

        $invite = app(WeddingPartnerInviteService::class)->createOrRefreshInvite(
            $wedding,
            $owner,
            'partner@example.com',
        );

        Livewire::test(PartnerInviteAccept::class, ['token' => $invite->token])
            ->set('name', 'Partner User')
            ->set('email', 'partner@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect(route('dashboard'));

        $partner = User::query()->where('email', 'partner@example.com')->first();

        $this->assertNotNull($partner);
        $this->assertTrue($partner->isPartnerOf($wedding));
        $this->assertSame($wedding->id, $partner->accessibleWedding()?->id);
        $this->assertSame($partner->id, $invite->fresh()->accepted_by_user_id);
    }

    public function test_existing_user_without_wedding_can_accept_invite(): void
    {
        $owner = User::factory()->create();
        $wedding = WeddingEvent::factory()->create(['user_id' => $owner->id]);
        $partner = User::factory()->create(['email' => 'partner@example.com']);

        $invite = WeddingPartnerInvite::query()->create([
            'wedding_event_id' => $wedding->id,
            'invited_by_user_id' => $owner->id,
            'email' => 'partner@example.com',
            'token' => 'test-token-123',
            'expires_at' => now()->addDays(7),
        ]);

        Livewire::actingAs($partner)
            ->test(PartnerInviteAccept::class, ['token' => $invite->token])
            ->call('accept')
            ->assertRedirect(route('dashboard'));

        $this->assertTrue($partner->fresh()->isPartnerOf($wedding));
    }

    public function test_user_with_existing_wedding_cannot_accept_invite(): void
    {
        $owner = User::factory()->create();
        $wedding = WeddingEvent::factory()->create(['user_id' => $owner->id]);
        $other = User::factory()->create(['email' => 'other@example.com']);
        WeddingEvent::factory()->create(['user_id' => $other->id]);

        $invite = WeddingPartnerInvite::query()->create([
            'wedding_event_id' => $wedding->id,
            'invited_by_user_id' => $owner->id,
            'email' => 'other@example.com',
            'token' => 'blocked-token',
            'expires_at' => now()->addDays(7),
        ]);

        Livewire::actingAs($other)
            ->test(PartnerInviteAccept::class, ['token' => $invite->token])
            ->call('accept')
            ->assertHasErrors(['email']);
    }

    public function test_partner_can_access_dashboard_wedding(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $wedding = WeddingEvent::factory()->create(['user_id' => $owner->id]);

        WeddingMember::query()->create([
            'wedding_event_id' => $wedding->id,
            'user_id' => $partner->id,
            'role' => WeddingMemberRole::Partner,
        ]);

        $this->actingAs($partner)
            ->get(route('dashboard.wedding'))
            ->assertOk()
            ->assertSee($wedding->groom_name, false);
    }

    public function test_owner_can_remove_partner(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $wedding = WeddingEvent::factory()->create(['user_id' => $owner->id]);

        WeddingMember::query()->create([
            'wedding_event_id' => $wedding->id,
            'user_id' => $partner->id,
            'role' => WeddingMemberRole::Partner,
        ]);

        Livewire::actingAs($owner)
            ->test(PlanningPartner::class)
            ->call('removePartner')
            ->assertHasNoErrors();

        $this->assertFalse($partner->fresh()->hasWeddingAccess());
        $this->assertFalse($wedding->fresh()->hasPartner());
    }

    public function test_partner_can_leave_wedding(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $wedding = WeddingEvent::factory()->create(['user_id' => $owner->id]);

        WeddingMember::query()->create([
            'wedding_event_id' => $wedding->id,
            'user_id' => $partner->id,
            'role' => WeddingMemberRole::Partner,
        ]);

        Livewire::actingAs($partner)
            ->test(PlanningPartner::class)
            ->call('leaveWedding')
            ->assertRedirect(route('dashboard'));

        $this->assertFalse($partner->fresh()->hasWeddingAccess());
    }

    public function test_wedding_is_accessible_by_owner_and_partner(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $wedding = WeddingEvent::factory()->create(['user_id' => $owner->id]);

        WeddingMember::query()->create([
            'wedding_event_id' => $wedding->id,
            'user_id' => $partner->id,
            'role' => WeddingMemberRole::Partner,
        ]);

        $this->assertTrue($wedding->isAccessibleBy($owner));
        $this->assertTrue($wedding->isAccessibleBy($partner));
        $this->assertSame($wedding->id, $owner->accessibleWedding()?->id);
        $this->assertSame($wedding->id, $partner->accessibleWedding()?->id);
    }
}

<?php

namespace Tests\Feature;

use App\Filament\App\Resources\MyWeddingResource\Pages\EditMyWedding;
use App\Filament\Resources\WeddingEvents\RelationManagers\GuestsRelationManager;
use App\Livewire\InvitationPage;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Support\Locale;
use App\Support\LocaleUrl;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class InvitationLocaleTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_new_wedding_inherits_owner_profile_locale(): void
    {
        $owner = User::factory()->create(['locale' => 'de']);
        $event = WeddingEvent::factory()->for($owner)->create();

        $this->assertSame('de', $event->invitation_locale);
        $this->assertSame('de', $event->invitationLocale());
    }

    public function test_new_wedding_falls_back_to_app_default_when_owner_locale_missing(): void
    {
        $owner = User::factory()->create(['locale' => null]);
        $event = WeddingEvent::factory()->for($owner)->create();

        $this->assertSame(Locale::default(), $event->invitation_locale);
    }

    public function test_public_and_personal_urls_append_resolved_locale(): void
    {
        $owner = User::factory()->create(['locale' => 'bs']);
        $event = WeddingEvent::factory()->for($owner)->create([
            'invitation_locale' => 'bs',
            'slug' => 'amir-amina',
        ]);
        $guest = Guest::factory()->for($event)->create([
            'token' => 'guest-token-123',
            'invitation_locale' => null,
        ]);
        $germanGuest = Guest::factory()->for($event)->create([
            'token' => 'guest-token-de',
            'invitation_locale' => 'de',
        ]);

        $this->assertSame(
            LocaleUrl::withLocale(url('/e/amir-amina'), 'bs'),
            $event->public_url,
        );
        $this->assertSame(
            LocaleUrl::withLocale(url('/e/amir-amina/guest-token-123'), 'bs'),
            $guest->personal_url,
        );
        $this->assertSame(
            LocaleUrl::withLocale(url('/e/amir-amina/guest-token-de'), 'de'),
            $germanGuest->personal_url,
        );
        $this->assertStringContainsString('locale=de', $germanGuest->personal_url);
    }

    public function test_guest_invitation_locale_falls_back_through_wedding_then_default(): void
    {
        $owner = User::factory()->create(['locale' => 'en']);
        $event = WeddingEvent::factory()->for($owner)->create([
            'invitation_locale' => 'en',
        ]);
        $guest = Guest::factory()->for($event)->create([
            'invitation_locale' => null,
        ]);

        $this->assertSame('en', $guest->invitationLocale());
        $this->assertSame('en', $guest->preferredLocale());

        $guest->update(['invitation_locale' => 'de']);
        $this->assertSame('de', $guest->fresh()->invitationLocale());
        $this->assertSame('de', $guest->fresh()->preferredLocale());

        $guest->update(['invitation_locale' => 'xx']);
        $this->assertSame('en', $guest->fresh()->invitationLocale());
    }

    public function test_invitation_query_locale_renders_german_without_changing_owner_profile(): void
    {
        $owner = User::factory()->create(['locale' => 'en']);
        $event = WeddingEvent::factory()->for($owner)->create([
            'invitation_locale' => 'bs',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('invitation.show', $event->slug).'?locale=de')
            ->assertOk()
            ->assertSee(__('invitation.rsvp', [], 'de'), false);

        $this->assertSame('en', $owner->fresh()->locale);
        $this->assertSame('de', session('locale'));
    }

    public function test_invitation_locale_switcher_does_not_persist_to_owner_profile(): void
    {
        $owner = User::factory()->create(['locale' => 'en']);
        $event = WeddingEvent::factory()->for($owner)->create([
            'invitation_locale' => 'bs',
            'is_active' => true,
        ]);

        $this->actingAs($owner);

        Livewire::test(InvitationPage::class, ['slug' => $event->slug])
            ->call('switchLocale', 'de')
            ->assertHasNoErrors();

        $this->assertSame('en', $owner->fresh()->locale);
        $this->assertSame('de', session('locale'));
    }

    public function test_manifest_start_url_includes_guest_invitation_locale(): void
    {
        $event = WeddingEvent::factory()->create([
            'invitation_locale' => 'bs',
            'slug' => 'manifest-couple',
        ]);
        $guest = Guest::factory()->for($event)->create([
            'token' => 'manifest-token',
            'invitation_locale' => 'de',
        ]);

        $this->get(route('invitation.manifest', [$event->slug, $guest->token]))
            ->assertOk()
            ->assertJsonPath('start_url', $guest->personal_url);

        $this->assertStringContainsString('locale=de', $guest->personal_url);
    }

    public function test_couple_can_update_wedding_invitation_locale(): void
    {
        $owner = User::factory()->create(['locale' => 'bs']);
        $event = WeddingEvent::factory()->for($owner)->create([
            'invitation_locale' => 'bs',
            'is_active' => true,
        ]);

        $this->actingAs($owner);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(EditMyWedding::class, ['record' => $event->getKey()])
            ->fillForm([
                'groom_name' => $event->groom_name,
                'bride_name' => $event->bride_name,
                'theme' => $event->theme->value,
                'template' => $event->template->value,
                'link_mode' => $event->link_mode->value,
                'wedding_date' => $event->wedding_date->format('Y-m-d H:i:s'),
                'invitation_locale' => 'de',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $event->fresh();
        $this->assertSame('de', $fresh->invitation_locale);
        $this->assertStringContainsString('locale=de', $fresh->public_url);
    }

    public function test_couple_can_set_and_clear_guest_invitation_locale_override(): void
    {
        $owner = User::factory()->create(['locale' => 'bs']);
        $event = WeddingEvent::factory()->for($owner)->create([
            'invitation_locale' => 'bs',
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create([
            'invitation_locale' => null,
        ]);

        $this->actingAs($owner);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(GuestsRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditMyWedding::class,
        ])
            ->callTableAction('edit', $guest, data: [
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'plus_one_allowed' => false,
                'invitation_locale' => 'de',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame('de', $guest->fresh()->invitation_locale);
        $this->assertStringContainsString('locale=de', $guest->fresh()->personal_url);

        Livewire::test(GuestsRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditMyWedding::class,
        ])
            ->callTableAction('edit', $guest, data: [
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'plus_one_allowed' => false,
                'invitation_locale' => null,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertNull($guest->fresh()->invitation_locale);
        $this->assertStringContainsString('locale=bs', $guest->fresh()->personal_url);
    }
}

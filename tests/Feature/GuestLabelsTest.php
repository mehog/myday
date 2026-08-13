<?php

namespace Tests\Feature;

use App\Filament\App\Pages\SeatingPlan;
use App\Filament\App\Resources\MyWeddingResource\Pages\EditMyWedding;
use App\Filament\Resources\WeddingEvents\RelationManagers\GuestsRelationManager;
use App\GuestLabel;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class GuestLabelsTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_couple_can_save_multiple_guest_labels(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create(['is_active' => true]);
        $guest = Guest::factory()->for($event)->create(['labels' => null]);

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
                'labels' => [
                    GuestLabel::Bride->value,
                    GuestLabel::Family->value,
                    GuestLabel::Friend->value,
                ],
            ])
            ->assertHasNoTableActionErrors();

        $fresh = $guest->fresh();

        $this->assertNotNull($fresh->labels);
        $this->assertEqualsCanonicalizing(
            [
                GuestLabel::Bride,
                GuestLabel::Family,
                GuestLabel::Friend,
            ],
            $fresh->labels->all(),
        );
    }

    public function test_couple_can_clear_guest_labels(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create(['is_active' => true]);
        $guest = Guest::factory()->for($event)->withLabels([
            GuestLabel::Groom,
            GuestLabel::Colleague,
        ])->create();

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
                'labels' => [],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertNull($guest->fresh()->labels);
    }

    public function test_guest_labels_filter_returns_matching_guests(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create(['is_active' => true]);

        $familyGuest = Guest::factory()->for($event)->withLabels([GuestLabel::Family])->create([
            'name' => 'Family Guest',
        ]);
        $brideGuest = Guest::factory()->for($event)->withLabels([GuestLabel::Bride, GuestLabel::Friend])->create([
            'name' => 'Bride Guest',
        ]);
        $unlabeledGuest = Guest::factory()->for($event)->create([
            'name' => 'Unlabeled Guest',
            'labels' => null,
        ]);

        $this->actingAs($owner);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(GuestsRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditMyWedding::class,
        ])
            ->filterTable('labels', [GuestLabel::Family->value])
            ->assertCanSeeTableRecords([$familyGuest])
            ->assertCanNotSeeTableRecords([$brideGuest, $unlabeledGuest]);

        Livewire::test(GuestsRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditMyWedding::class,
        ])
            ->filterTable('labels', [GuestLabel::Bride->value, GuestLabel::Family->value])
            ->assertCanSeeTableRecords([$familyGuest, $brideGuest])
            ->assertCanNotSeeTableRecords([$unlabeledGuest]);

        Livewire::test(GuestsRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditMyWedding::class,
        ])
            ->filterTable('labels', ['none'])
            ->assertCanSeeTableRecords([$unlabeledGuest])
            ->assertCanNotSeeTableRecords([$familyGuest, $brideGuest]);
    }

    public function test_seating_plan_guest_list_includes_translated_labels(): void
    {
        app()->setLocale('en');

        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->withLabels([
            GuestLabel::Bride,
            GuestLabel::Teenager,
        ])->create([
            'name' => 'Labeled Teen',
            'rsvp_status' => RsvpStatus::Yes,
        ]);

        $this->actingAs($owner);

        $guests = Livewire::test(SeatingPlan::class)
            ->instance()
            ->getGuests();

        $entry = $guests->firstWhere('id', $guest->id);

        $this->assertNotNull($entry);
        $this->assertSame(
            [
                GuestLabel::Bride->label(),
                GuestLabel::Teenager->label(),
            ],
            $entry['labels'],
        );
    }

    public function test_guest_label_translations_are_available(): void
    {
        foreach (['en', 'bs', 'hr', 'de'] as $locale) {
            app()->setLocale($locale);

            foreach (GuestLabel::cases() as $label) {
                $this->assertNotSame(
                    'guests.label_'.$label->value,
                    $label->label(),
                    "Missing translation for {$label->value} in {$locale}",
                );
            }
        }

        $this->assertSame('teenager', GuestLabel::Teenager->value);
        $this->assertFalse(
            collect(GuestLabel::cases())->contains(fn (GuestLabel $label): bool => $label->value === 'child'),
        );
    }
}

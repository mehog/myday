<?php

namespace Tests\Feature;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\LinkMode;
use App\Livewire\Onboarding\OnboardingPreview;
use App\Livewire\Onboarding\WeddingOnboarding;
use App\Models\Guest;
use App\Models\ScheduleItem;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Models\WeddingLocation;
use App\PlanTier;
use App\Support\Locale;
use App\Support\MetaPixel;
use App\Support\OnboardingSongs;
use App\Support\OnboardingSteps;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class WeddingOnboardingTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_mount_prefills_theme_template_and_reveal_from_query(): void
    {
        Livewire::withQueryParams([
            'theme' => InvitationTheme::RoyalWedding->value,
            'template' => InvitationTemplate::Editorial->value,
            'reveal' => InvitationReveal::Envelope->value,
        ])
            ->test(WeddingOnboarding::class)
            ->assertSet('theme', InvitationTheme::RoyalWedding->value)
            ->assertSet('template', InvitationTemplate::Editorial->value)
            ->assertSet('reveal_animation', InvitationReveal::Envelope->value);
    }

    public function test_mount_maps_reveal_none_to_empty_animation(): void
    {
        Livewire::withQueryParams([
            'theme' => InvitationTheme::AmberGold->value,
            'template' => InvitationTemplate::Classic->value,
            'reveal' => 'none',
        ])
            ->test(WeddingOnboarding::class)
            ->assertSet('theme', InvitationTheme::AmberGold->value)
            ->assertSet('template', InvitationTemplate::Classic->value)
            ->assertSet('reveal_animation', '');
    }

    public function test_mount_ignores_invalid_style_query_params(): void
    {
        Livewire::withQueryParams([
            'theme' => 'not-a-theme',
            'template' => 'not-a-template',
            'reveal' => 'not-a-reveal',
        ])
            ->test(WeddingOnboarding::class)
            ->assertSet('theme', '')
            ->assertSet('template', '')
            ->assertSet('reveal_animation', '');
    }

    public function test_names_and_date_advance_to_rsvp_tip_without_incrementing_past_counted_steps(): void
    {
        Livewire::test(WeddingOnboarding::class)
            ->assertSet('step', 'names')
            ->assertSee('1/'.OnboardingSteps::countedTotal())
            ->set('groom_name', 'Amir')
            ->set('bride_name', 'Amina')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 'date')
            ->set('wedding_date', now()->addMonths(3)->toDateString())
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 'tip-rsvp')
            ->assertSee(__('onboarding.tip_rsvp_title'))
            ->assertSee('2/'.OnboardingSteps::countedTotal())
            ->call('nextStep')
            ->assertSet('step', 'theme');
    }

    public function test_select_theme_and_template_auto_advance(): void
    {
        Livewire::test(WeddingOnboarding::class)
            ->set('step', 'theme')
            ->call('selectTheme', InvitationTheme::AmberGold->value)
            ->assertSet('theme', InvitationTheme::AmberGold->value)
            ->assertSet('step', 'template')
            ->call('selectTemplate', InvitationTemplate::Classic->value)
            ->assertSet('template', InvitationTemplate::Classic->value)
            ->assertSet('step', 'reveal');
    }

    public function test_skip_optional_reveal_clears_animation(): void
    {
        Livewire::test(WeddingOnboarding::class)
            ->set('step', 'reveal')
            ->set('reveal_animation', InvitationReveal::Envelope->value)
            ->call('skipStep')
            ->assertSet('reveal_animation', '')
            ->assertSet('step', 'location');
    }

    public function test_interstitial_tip_seating_navigates_forward_and_back(): void
    {
        Livewire::test(WeddingOnboarding::class)
            ->set('step', 'tip-seating')
            ->assertSee(__('onboarding.tip_seating_body'))
            ->call('nextStep')
            ->assertSet('step', 'guests')
            ->call('previousStep')
            ->assertSet('step', 'tip-seating')
            ->call('previousStep')
            ->assertSet('step', 'schedule');
    }

    public function test_submit_persists_optional_setup_data(): void
    {
        Notification::fake();

        Livewire::test(WeddingOnboarding::class)
            ->set('groom_name', 'Amir')
            ->set('bride_name', 'Amina')
            ->set('wedding_date', now()->addMonths(3)->toDateString())
            ->set('theme', InvitationTheme::AmberGold->value)
            ->set('template', InvitationTemplate::Classic->value)
            ->set('reveal_animation', InvitationReveal::WaxSeal->value)
            ->set('location_name', 'Garden Hall')
            ->set('location_address', 'Sarajevo')
            ->set('motto', 'Forever yes')
            ->set('music_url', 'https://www.youtube.com/watch?v=2Vv-BfVoq4g')
            ->set('scheduleItems', [
                ['time' => '12:00', 'title' => 'Ceremony', 'description' => 'Welcome'],
                ['time' => '', 'title' => '', 'description' => ''],
            ])
            ->set('guests', [
                ['name' => 'Sara Softic', 'email' => 'sara@example.com', 'plus_one_allowed' => true],
                ['name' => '', 'email' => '', 'plus_one_allowed' => false],
            ])
            ->set('your_name', 'Amir Softic')
            ->set('email', 'amir@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'amir@example.com')->first();
        $this->assertNotNull($user);

        $wedding = WeddingEvent::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($wedding);
        $this->assertSame(InvitationReveal::WaxSeal, $wedding->reveal_animation);
        $this->assertSame(LinkMode::TokenOnly, $wedding->link_mode);
        $this->assertSame(PlanTier::Free, $wedding->plan_tier);
        $this->assertSame(50, $wedding->guest_limit);
        $this->assertTrue($wedding->is_active);
        $this->assertSame('https://www.youtube.com/watch?v=2Vv-BfVoq4g', $wedding->music_url);
        $this->assertSame('Forever yes', $wedding->motto);
        $this->assertSame('Garden Hall', $wedding->location_name);

        $this->assertSame(1, WeddingLocation::query()->where('wedding_event_id', $wedding->id)->count());
        $this->assertSame(1, ScheduleItem::query()->where('wedding_event_id', $wedding->id)->count());
        $this->assertSame(1, Guest::query()->where('wedding_event_id', $wedding->id)->count());

        $guest = Guest::query()->where('wedding_event_id', $wedding->id)->first();
        $this->assertSame('Sara Softic', $guest->name);
        $this->assertTrue((bool) $guest->plus_one_allowed);

        Notification::assertSentTo($user, VerifyEmail::class);

        $this->assertSame([
            'name' => 'CompleteRegistration',
        ], session(MetaPixel::EVENT_KEY));
    }

    public function test_submit_allows_empty_entry_animation(): void
    {
        Notification::fake();

        Livewire::test(WeddingOnboarding::class)
            ->set('groom_name', 'Amir')
            ->set('bride_name', 'Amina')
            ->set('wedding_date', now()->addMonths(3)->toDateString())
            ->set('theme', InvitationTheme::AmberGold->value)
            ->set('template', InvitationTemplate::Classic->value)
            ->set('reveal_animation', '')
            ->set('your_name', 'Amir Softic')
            ->set('email', 'amina@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('verification.notice'));

        $wedding = WeddingEvent::query()->where('groom_name', 'Amir')->first();
        $this->assertNotNull($wedding);
        $this->assertNull($wedding->reveal_animation);
    }

    public function test_preview_route_shows_missing_state_without_draft(): void
    {
        Livewire::test(OnboardingPreview::class)
            ->assertSet('missingDraft', true)
            ->assertSee(__('onboarding.preview_missing_title'));
    }

    public function test_preview_route_renders_from_session_draft(): void
    {
        session()->put(config('onboarding.draft_session_key'), [
            'groom_name' => 'Amir',
            'bride_name' => 'Amina',
            'wedding_date' => now()->addMonths(3)->toDateString(),
            'theme' => InvitationTheme::AmberGold->value,
            'template' => InvitationTemplate::Classic->value,
            'reveal_animation' => '',
            'location_name' => 'Garden Hall',
            'location_address' => 'Sarajevo',
            'motto' => 'Forever',
            'music_url' => '',
            'hero_temp_url' => null,
            'schedule_items' => [
                ['time' => '12:00', 'title' => 'Ceremony', 'description' => ''],
            ],
            'invitation_locale' => 'en',
        ]);

        Livewire::test(OnboardingPreview::class)
            ->assertSet('missingDraft', false)
            ->assertSet('invitationRevealed', true)
            ->assertSee('Amir')
            ->assertSee('Amina')
            ->assertSee(__('onboarding.preview_draft_banner'))
            ->assertDontSee('env-photo-stage', false)
            ->assertDontSee('opacity:0;pointer-events:none', false);
    }

    public function test_preview_route_plays_selected_entry_animation(): void
    {
        session()->put(config('onboarding.draft_session_key'), [
            'groom_name' => 'Amir',
            'bride_name' => 'Amina',
            'wedding_date' => now()->addMonths(3)->toDateString(),
            'theme' => InvitationTheme::AmberGold->value,
            'template' => InvitationTemplate::Classic->value,
            'reveal_animation' => InvitationReveal::Envelope->value,
            'location_name' => 'Garden Hall',
            'location_address' => 'Sarajevo',
            'motto' => 'Forever',
            'music_url' => '',
            'hero_temp_url' => null,
            'schedule_items' => [],
            'invitation_locale' => 'en',
        ]);

        Livewire::test(OnboardingPreview::class)
            ->assertSet('missingDraft', false)
            ->assertSet('invitationRevealed', false)
            ->assertSee('env-photo-stage', false)
            ->assertSee('env-photo-envelope', false)
            ->assertSee('nasdan-envelope-closed.webp', false)
            ->assertSee('opacity:0;pointer-events:none', false)
            ->assertSee(__('onboarding.preview_draft_banner'));
    }

    public function test_preview_rsvp_is_non_interactive_and_respond_is_safe(): void
    {
        session()->put(config('onboarding.draft_session_key'), [
            'groom_name' => 'Amir',
            'bride_name' => 'Amina',
            'wedding_date' => now()->addMonths(3)->toDateString(),
            'theme' => InvitationTheme::AmberGold->value,
            'template' => InvitationTemplate::Classic->value,
            'reveal_animation' => '',
            'location_name' => 'Garden Hall',
            'location_address' => 'Sarajevo',
            'motto' => 'Forever',
            'music_url' => '',
            'hero_temp_url' => null,
            'schedule_items' => [],
            'invitation_locale' => 'en',
        ]);

        Livewire::test(OnboardingPreview::class)
            ->assertSet('isTokenOnlyPreview', true)
            ->assertSee(__('invitation.token_only_preview_rsvp'))
            ->assertDontSee('$wire.respond', false)
            ->assertDontSee(__('invitation.yes_attending'))
            ->assertDontSee(__('invitation.your_name'))
            ->call('respond', 'yes');

        $this->assertDatabaseCount('guests', 0);
    }

    public function test_open_preview_stores_draft_and_shows_modal(): void
    {
        Livewire::test(WeddingOnboarding::class)
            ->set('groom_name', 'Amir')
            ->set('bride_name', 'Amina')
            ->set('wedding_date', now()->addMonths(3)->toDateString())
            ->set('theme', InvitationTheme::AmberGold->value)
            ->set('template', InvitationTemplate::Classic->value)
            ->set('step', 'review')
            ->call('openPreview')
            ->assertDispatched('invitation-preview-open', function (string $name, array $params): bool {
                return $name === 'invitation-preview-open'
                    && ($params['url'] ?? null) === route('onboarding.preview')
                    && ($params['title'] ?? null) === __('onboarding.preview_modal_title');
            })
            ->assertSet('previewError', null);

        $draft = session(config('onboarding.draft_session_key'));
        $this->assertIsArray($draft);
        $this->assertSame('Amir', $draft['groom_name']);
    }

    public function test_open_preview_blocks_when_required_fields_missing(): void
    {
        Livewire::test(WeddingOnboarding::class)
            ->set('step', 'review')
            ->call('openPreview')
            ->assertNotDispatched('invitation-preview-open')
            ->assertSet('previewError', __('onboarding.preview_incomplete'));
    }

    public function test_progress_survives_remount_from_session(): void
    {
        Livewire::test(WeddingOnboarding::class)
            ->set('groom_name', 'Amir')
            ->set('bride_name', 'Amina')
            ->set('wedding_date', now()->addMonths(3)->toDateString())
            ->set('theme', InvitationTheme::AmberGold->value)
            ->set('template', InvitationTemplate::Classic->value)
            ->set('your_name', 'Amir Softic')
            ->set('email', 'amir@example.com')
            ->set('step', 'review')
            ->call('openPreview');

        $this->assertNotEmpty(session(config('onboarding.progress_session_key')));

        Livewire::withQueryParams(['step' => 'review'])
            ->test(WeddingOnboarding::class)
            ->assertSet('groom_name', 'Amir')
            ->assertSet('bride_name', 'Amina')
            ->assertSet('theme', InvitationTheme::AmberGold->value)
            ->assertSet('your_name', 'Amir Softic')
            ->assertSet('email', 'amir@example.com')
            ->assertSet('step', 'review')
            ->assertSee('Amir')
            ->assertSee('Amina')
            ->assertSee('amir@example.com');
    }

    public function test_inaccessible_review_step_snaps_to_names(): void
    {
        Livewire::withQueryParams(['step' => 'review'])
            ->test(WeddingOnboarding::class)
            ->assertSet('step', 'names');
    }

    public function test_submit_with_missing_theme_jumps_to_theme_step(): void
    {
        Livewire::test(WeddingOnboarding::class)
            ->set('groom_name', 'Amir')
            ->set('bride_name', 'Amina')
            ->set('wedding_date', now()->addMonths(3)->toDateString())
            ->set('theme', '')
            ->set('template', InvitationTemplate::Classic->value)
            ->set('your_name', 'Amir Softic')
            ->set('email', 'amir-theme@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('step', 'review')
            ->call('submit')
            ->assertHasErrors(['theme'])
            ->assertSet('step', 'theme')
            ->assertSet('submitError', __('onboarding.submit_fix_errors'));
    }

    public function test_select_motto_fills_textarea(): void
    {
        $preset = __('onboarding.motto_preset_1');

        Livewire::test(WeddingOnboarding::class)
            ->set('step', 'motto')
            ->call('selectMotto', $preset)
            ->assertSet('motto', $preset)
            ->assertSee($preset);
    }

    public function test_song_step_shows_scrollable_song_list(): void
    {
        $catalogCount = count(OnboardingSongs::forLocale());

        $component = Livewire::test(WeddingOnboarding::class)
            ->set('step', 'song');

        $this->assertCount($catalogCount, $component->get('songSuggestions'));

        $html = $component->html();
        $this->assertSame($catalogCount, substr_count($html, 'data-song-pick'));
        $this->assertStringContainsString('h-[250px]', $html);
        $this->assertStringContainsString('overflow-y-auto', $html);
        $component->assertDontSee('Volio bi\' da te ne volim');
        $component->assertDontSee('Tugo nesrećo');
    }

    public function test_song_suggestions_reshuffle_on_full_page_remount(): void
    {
        $catalogCount = count(OnboardingSongs::forLocale());
        $first = Livewire::test(WeddingOnboarding::class)->get('songSuggestions');
        $this->assertCount($catalogCount, $first);

        $seenDifferent = false;

        for ($i = 0; $i < 12; $i++) {
            $next = Livewire::test(WeddingOnboarding::class)->get('songSuggestions');

            if ($next !== $first) {
                $seenDifferent = true;
                break;
            }
        }

        $this->assertTrue($seenDifferent, 'Expected song suggestions to reshuffle across remounts.');
    }

    public function test_song_search_lists_matches_without_teaser(): void
    {
        Locale::set('bs', persistToUser: false);

        Livewire::test(WeddingOnboarding::class)
            ->set('step', 'song')
            ->set('song_query', 'Dino')
            ->assertSee('Zauvijek ovako')
            ->assertSee('Dino Merlin');
    }

    public function test_selected_song_is_pinned_to_top_of_list(): void
    {
        $catalog = OnboardingSongs::forLocale();
        $this->assertNotEmpty($catalog);

        $selected = $catalog[count($catalog) - 1];

        $html = Livewire::test(WeddingOnboarding::class)
            ->set('step', 'song')
            ->set('music_url', $selected['url'])
            ->html();

        if (! preg_match('/data-song-pick[\s\S]*?<\/button>/', $html, $match)) {
            $this->fail('Expected at least one song pick button.');
        }

        $this->assertStringContainsString($selected['title'], $match[0]);
    }

    public function test_regional_songs_only_shown_for_bs_and_hr_locales(): void
    {
        Locale::set('bs', persistToUser: false);
        $bsCatalog = OnboardingSongs::forLocale('bs');
        $this->assertTrue(collect($bsCatalog)->contains(fn (array $song): bool => $song['id'] === 'DBql7oBK-fs'));

        Locale::set('en', persistToUser: false);
        $enCatalog = OnboardingSongs::forLocale('en');
        $this->assertFalse(collect($enCatalog)->contains(fn (array $song): bool => $song['id'] === 'DBql7oBK-fs'));
        $this->assertTrue(collect($enCatalog)->contains(fn (array $song): bool => $song['id'] === '2Vv-BfVoq4g'));
        $this->assertTrue(collect($enCatalog)->contains(fn (array $song): bool => $song['id'] === 'LyYAQHDMqfA'));

        Livewire::test(WeddingOnboarding::class)
            ->call('switchLocale', 'en')
            ->set('step', 'song')
            ->assertDontSee('Zauvijek ovako')
            ->assertSee('Perfect');
    }
}

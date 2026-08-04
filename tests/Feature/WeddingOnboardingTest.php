<?php

namespace Tests\Feature;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\Livewire\Onboarding\WeddingOnboarding;
use App\Models\User;
use App\Models\WeddingEvent;
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

    public function test_step_one_accepts_optional_entry_animation_and_shows_it_on_review(): void
    {
        Livewire::test(WeddingOnboarding::class)
            ->set('groom_name', 'Amir')
            ->set('bride_name', 'Amina')
            ->set('wedding_date', now()->addMonths(3)->toDateString())
            ->set('theme', InvitationTheme::AmberGold->value)
            ->set('template', InvitationTemplate::Classic->value)
            ->set('reveal_animation', InvitationReveal::Envelope->value)
            ->assertSee(__('onboarding.design_changeable_note'))
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 2)
            ->set('your_name', 'Amir Softic')
            ->set('email', 'amir@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 3)
            ->assertSee(__('app.reveal_envelope'))
            ->assertSee(__('onboarding.review_after_signup_note'));
    }

    public function test_submit_persists_selected_entry_animation(): void
    {
        Notification::fake();

        Livewire::test(WeddingOnboarding::class)
            ->set('groom_name', 'Amir')
            ->set('bride_name', 'Amina')
            ->set('wedding_date', now()->addMonths(3)->toDateString())
            ->set('theme', InvitationTheme::AmberGold->value)
            ->set('template', InvitationTemplate::Classic->value)
            ->set('reveal_animation', InvitationReveal::WaxSeal->value)
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

        Notification::assertSentTo($user, VerifyEmail::class);
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
}

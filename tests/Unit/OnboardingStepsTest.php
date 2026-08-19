<?php

namespace Tests\Unit;

use App\Support\OnboardingSteps;
use PHPUnit\Framework\TestCase;

class OnboardingStepsTest extends TestCase
{
    public function test_counted_total_excludes_tips(): void
    {
        $this->assertSame(13, OnboardingSteps::countedTotal());
        $this->assertFalse(OnboardingSteps::isCounted('tip-rsvp'));
        $this->assertTrue(OnboardingSteps::isCounted('names'));
        $this->assertTrue(OnboardingSteps::isTip('tip-menus'));
    }

    public function test_counted_position_freezes_on_tips(): void
    {
        $this->assertSame(1, OnboardingSteps::countedPosition('names'));
        $this->assertSame(2, OnboardingSteps::countedPosition('date'));
        $this->assertSame(2, OnboardingSteps::countedPosition('tip-rsvp'));
        $this->assertSame(3, OnboardingSteps::countedPosition('theme'));
    }

    public function test_next_and_previous(): void
    {
        $this->assertSame('date', OnboardingSteps::next('names'));
        $this->assertSame('names', OnboardingSteps::previous('date'));
        $this->assertNull(OnboardingSteps::previous('names'));
        $this->assertNull(OnboardingSteps::next('review'));
    }

    public function test_can_access_and_first_incomplete(): void
    {
        $empty = [];
        $this->assertTrue(OnboardingSteps::canAccess('names', $empty));
        $this->assertFalse(OnboardingSteps::canAccess('review', $empty));
        $this->assertSame('names', OnboardingSteps::firstIncompleteStep($empty));

        $partial = [
            'groom_name' => 'Amir',
            'bride_name' => 'Amina',
            'wedding_date' => '2026-12-01',
        ];
        $this->assertTrue(OnboardingSteps::canAccess('theme', $partial));
        $this->assertFalse(OnboardingSteps::canAccess('template', $partial));
        $this->assertSame('theme', OnboardingSteps::firstIncompleteStep($partial));

        $styled = [
            ...$partial,
            'theme' => 'amber-gold',
            'template' => 'classic',
        ];
        $this->assertSame('location', OnboardingSteps::firstIncompleteStep($styled));
        $this->assertTrue(OnboardingSteps::canAccess('location', $styled));
        $this->assertFalse(OnboardingSteps::canAccess('review', $styled));

        $withoutSong = [
            ...$styled,
            'location_name' => 'Garden Hall',
            'location_address' => 'Sarajevo',
            'motto' => 'Forever',
            'has_hero_image' => true,
        ];
        $this->assertSame('song', OnboardingSteps::firstIncompleteStep($withoutSong));
        $this->assertFalse(OnboardingSteps::canAccess('review', $withoutSong));
    }

    public function test_step_for_field(): void
    {
        $this->assertSame('names', OnboardingSteps::stepForField('groom_name'));
        $this->assertSame('theme', OnboardingSteps::stepForField('theme'));
        $this->assertSame('account', OnboardingSteps::stepForField('password'));
    }
}

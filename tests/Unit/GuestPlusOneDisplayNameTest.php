<?php

namespace Tests\Unit;

use App\Models\Guest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuestPlusOneDisplayNameTest extends TestCase
{
    #[Test]
    public function it_returns_null_when_no_plus_one_name_is_set(): void
    {
        $guest = new Guest([
            'plus_one_name' => null,
            'plus_one_seating_name' => 'Formal Name',
        ]);

        $this->assertNull($guest->plusOneDisplayName());
    }

    #[Test]
    public function it_falls_back_to_guest_entered_plus_one_name(): void
    {
        $guest = new Guest([
            'plus_one_name' => '💙Velid (ljubav)💙',
            'plus_one_seating_name' => null,
        ]);

        $this->assertSame('💙Velid (ljubav)💙', $guest->plusOneDisplayName());
    }

    #[Test]
    public function it_prefers_the_formal_seating_name_when_present(): void
    {
        $guest = new Guest([
            'plus_one_name' => '💙Velid (ljubav)💙',
            'plus_one_seating_name' => 'Velid Softić',
        ]);

        $this->assertSame('Velid Softić', $guest->plusOneDisplayName());
    }
}

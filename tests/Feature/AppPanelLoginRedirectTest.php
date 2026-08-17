<?php

namespace Tests\Feature;

use App\Filament\App\Pages\Auth\Login;
use App\Models\User;
use App\Support\DashboardNav;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class AppPanelLoginRedirectTest extends TestCase
{
    use RefreshInMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('app'));
    }

    public function test_couple_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'email' => 'couple@example.com',
            'password' => 'password',
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect(DashboardNav::homeUrl());
    }

    public function test_already_authenticated_couple_visiting_login_goes_to_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/app/login')
            ->assertRedirect(DashboardNav::homeUrl());
    }
}

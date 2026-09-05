<?php

namespace Tests\Feature;

use App\Filament\App\Pages\PricingPage;
use App\Filament\App\Pages\ReferralsPage;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class AppPanelUserMenuTest extends TestCase
{
    use RefreshInMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('app'));
    }

    public function test_pricing_and_referrals_are_in_user_menu_after_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->assertFalse(PricingPage::shouldRegisterNavigation());
        $this->assertFalse(ReferralsPage::shouldRegisterNavigation());

        $menuItems = array_values(Filament::getUserMenuItems());
        $names = array_map(fn (Action $item): string => $item->getName(), $menuItems);

        $this->assertSame(['new_dashboard', 'profile', 'pricing', 'referrals', 'logout'], $names);

        $this->assertSame(__('dashboard.new_dashboard'), $menuItems[0]->getLabel());
        $this->assertSame(route('dashboard'), $menuItems[0]->getUrl());

        $this->assertSame(__('pricing.nav_label'), $menuItems[2]->getLabel());
        $this->assertSame(route('dashboard.pricing'), $menuItems[2]->getUrl());

        $this->assertSame(__('referrals.nav_label'), $menuItems[3]->getLabel());
        $this->assertSame(route('dashboard.referrals'), $menuItems[3]->getUrl());

        $navigationUrls = collect(Filament::getNavigation())
            ->flatMap(fn (NavigationGroup $group) => $group->getItems())
            ->map(fn (NavigationItem $item): ?string => $item->getUrl())
            ->filter()
            ->values()
            ->all();

        $this->assertNotContains(PricingPage::getUrl(), $navigationUrls);
        $this->assertNotContains(ReferralsPage::getUrl(), $navigationUrls);
    }

    public function test_pricing_user_menu_label_is_localized(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $expected = [
            'en' => 'Plans',
            'bs' => 'Paketi',
            'de' => 'Tarife',
            'hr' => 'Paketi',
            'sr_Latn' => 'Paketi',
        ];

        foreach ($expected as $locale => $label) {
            app()->setLocale($locale);

            $pricingItem = Filament::getUserMenuItems()['pricing'] ?? null;

            $this->assertInstanceOf(Action::class, $pricingItem);
            $this->assertSame($label, $pricingItem->getLabel());
        }
    }

    public function test_pricing_and_referrals_pages_remain_accessible(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/pricing')
            ->assertOk();

        $this->actingAs($user)
            ->get('/app/referrals')
            ->assertOk();
    }
}

<?php

namespace App\Livewire;

use App\PlanTier;
use App\PricingRegion;
use App\Support\DemoInvitationExamples;
use App\Support\DemoInvitationUrl;
use App\Support\DodoCatalog;
use App\Support\Locale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.landing')]
class LandingPage extends Component
{
    public function switchLocale(string $locale): void
    {
        Locale::set($locale);
    }

    public function render()
    {
        $demos = $this->loadDemos();
        $pricingRegion = PricingRegion::forVisitor();
        $pricingPlans = $this->pricingPlans($pricingRegion);

        return view('livewire.landing-page', compact('demos', 'pricingPlans'))
            ->title(__('landing.meta_title'));
    }

    /**
     * @return list<array{tier: string, name: string, guests: string, price: string, highlighted: bool}>
     */
    private function pricingPlans(PricingRegion $region): array
    {
        return array_map(function (array $plan) use ($region): array {
            $tier = $plan['tier'];

            return [
                'tier' => $tier->value,
                'name' => __('landing.pricing_plan_'.$tier->value.'_name'),
                'guests' => __('landing.pricing_plan_'.$tier->value.'_guests'),
                'price' => $tier === PlanTier::Free
                    ? __('landing.pricing_plan_free_price')
                    : $plan['price'].' '.$region->currency(),
                'highlighted' => $plan['highlighted'],
            ];
        }, DodoCatalog::displayPlansForRegion($region));
    }

    /**
     * @return list<array{title: string, previewUrl: string, openUrl: string}>
     */
    private function loadDemos(): array
    {
        $locale = app()->getLocale();
        $host = DemoInvitationUrl::resolveDemoHost($locale);

        return array_map(
            fn (array $example): array => DemoInvitationUrl::fromExample(
                $example,
                $host['slug'],
                $locale,
                $host['guestToken'],
            ),
            DemoInvitationExamples::featured(),
        );
    }
}

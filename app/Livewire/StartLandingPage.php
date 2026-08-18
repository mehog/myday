<?php

namespace App\Livewire;

use App\PlanTier;
use App\PricingRegion;
use App\Support\DemoInvitationExamples;
use App\Support\DemoInvitationUrl;
use App\Support\DodoCatalog;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.start')]
class StartLandingPage extends Component
{
    public function render()
    {
        $locale = app()->getLocale();
        $demos = $this->loadDemos($locale);
        $pricingPlans = $this->pricingPlans(PricingRegion::FirstWorld);

        return view('livewire.start-landing-page', compact('demos', 'pricingPlans'))
            ->title(__('start.meta_title'))
            ->layoutData([
                'pageTitle' => __('start.meta_title'),
                'pageDescription' => __('start.meta_description'),
                'canonicalUrl' => url('/start'),
            ]);
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
                'name' => __('start.pricing_plan_'.$tier->value.'_name'),
                'guests' => __('start.pricing_plan_'.$tier->value.'_guests'),
                'price' => $tier === PlanTier::Free
                    ? __('start.pricing_plan_free_price')
                    : $plan['price'].' '.$region->currency(),
                'highlighted' => $plan['highlighted'],
            ];
        }, DodoCatalog::displayPlansForRegion($region));
    }

    /**
     * @return list<array{title: string, previewUrl: string, openUrl: string}>
     */
    private function loadDemos(string $locale): array
    {
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

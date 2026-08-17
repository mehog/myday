<?php

namespace App\Http\Controllers;

use App\PlanTier;
use App\PricingRegion;
use App\Support\DodoCatalog;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PackagePageController extends Controller
{
    public function index(): View
    {
        $region = $this->displayRegion();
        $plans = $this->plansForDisplay($region);

        return view('landing.packages.index', [
            'plans' => $plans,
            'region' => $region,
            'pageTitle' => __('packages.index.meta_title'),
            'pageDescription' => __('packages.index.meta_description'),
            'canonicalUrl' => route('packages.index'),
            'jsonLd' => $this->indexJsonLd($plans, $region),
        ]);
    }

    public function show(string $tier): View
    {
        $planTier = PlanTier::tryFrom($tier);

        if ($planTier === null || (! $planTier->isPurchasable() && $planTier !== PlanTier::Free)) {
            throw new NotFoundHttpException;
        }

        $region = $this->displayRegion();
        $plans = $this->plansForDisplay($region);
        $plan = collect($plans)->firstWhere('tier', $planTier);

        if ($plan === null) {
            throw new NotFoundHttpException;
        }

        return view('landing.packages.show', [
            'plan' => $plan,
            'plans' => $plans,
            'region' => $region,
            'pageTitle' => __('packages.tiers.'.$planTier->value.'.meta_title'),
            'pageDescription' => __('packages.tiers.'.$planTier->value.'.meta_description'),
            'canonicalUrl' => route('packages.show', ['tier' => $planTier->value]),
            'jsonLd' => $this->showJsonLd($plan, $region),
        ]);
    }

    private function displayRegion(): PricingRegion
    {
        return PricingRegion::forVisitor();
    }

    /**
     * @return list<array{
     *     tier: PlanTier,
     *     price: int,
     *     currency: string,
     *     guest_limit: ?int,
     *     highlighted: bool,
     *     name: string,
     *     guests_label: string,
     *     price_label: string,
     *     url: string
     * }>
     */
    private function plansForDisplay(PricingRegion $region): array
    {
        return array_map(function (array $plan) use ($region): array {
            /** @var PlanTier $tier */
            $tier = $plan['tier'];

            return [
                ...$plan,
                'name' => __('landing.pricing_plan_'.$tier->value.'_name'),
                'guests_label' => $tier->guestLimit() === null
                    ? __('pricing.guests_unlimited')
                    : __('pricing.guests_up_to', ['count' => $tier->guestLimit()]),
                'price_label' => $tier === PlanTier::Free
                    ? __('landing.pricing_plan_free_price')
                    : $plan['price'].' '.$region->currency(),
                'url' => route('packages.show', ['tier' => $tier->value]),
            ];
        }, DodoCatalog::displayPlansForRegion($region));
    }

    /**
     * @param  list<array<string, mixed>>  $plans
     * @return list<array<string, mixed>>
     */
    private function indexJsonLd(array $plans, PricingRegion $region): array
    {
        $itemList = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => __('packages.index.heading'),
            'itemListElement' => collect($plans)->values()->map(fn (array $plan, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => $plan['url'],
                'name' => $plan['name'],
            ])->all(),
        ];

        $products = array_map(
            fn (array $plan): array => $this->productSchema($plan, $region),
            $plans,
        );

        return [$itemList, ...$products];
    }

    /**
     * @param  array<string, mixed>  $plan
     * @return list<array<string, mixed>>
     */
    private function showJsonLd(array $plan, PricingRegion $region): array
    {
        /** @var PlanTier $tier */
        $tier = $plan['tier'];
        $schemas = [
            $this->productSchema($plan, $region),
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => __('packages.nav_home'),
                        'item' => route('home'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => __('packages.nav_packages'),
                        'item' => route('packages.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $plan['name'],
                        'item' => $plan['url'],
                    ],
                ],
            ],
        ];

        $faqEntities = [];
        foreach (range(1, 4) as $i) {
            $question = __('packages.tiers.'.$tier->value.'.faq_'.$i.'_q');
            $answer = __('packages.tiers.'.$tier->value.'.faq_'.$i.'_a');

            if ($question === 'packages.tiers.'.$tier->value.'.faq_'.$i.'_q') {
                continue;
            }

            $faqEntities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        if ($faqEntities !== []) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqEntities,
            ];
        }

        return $schemas;
    }

    /**
     * @param  array<string, mixed>  $plan
     * @return array<string, mixed>
     */
    private function productSchema(array $plan, PricingRegion $region): array
    {
        /** @var PlanTier $tier */
        $tier = $plan['tier'];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => config('app.name').' '.$plan['name'],
            'description' => __('packages.tiers.'.$tier->value.'.summary'),
            'brand' => [
                '@type' => 'Brand',
                'name' => config('app.name'),
            ],
            'category' => 'Digital wedding invitation',
            'offers' => [
                '@type' => 'Offer',
                'url' => $plan['url'],
                'price' => (string) $plan['price'],
                'priceCurrency' => $region->currency(),
                'availability' => 'https://schema.org/InStock',
                'priceValidUntil' => now()->addYear()->toDateString(),
            ],
        ];
    }
}

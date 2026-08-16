<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\DodoPayment;
use App\PlanTier;
use App\Support\DodoCatalog;
use Illuminate\Support\Collection;
use Livewire\Component;

class Pricing extends Component
{
    use RendersDashboard;

    public function mount(): void
    {
        $checkout = request()->query('checkout');

        if ($checkout === 'return') {
            session()->flash('success', __('pricing.return_pending_title').' — '.__('pricing.return_pending_body'));
        }

        if ($checkout === 'cancel') {
            session()->flash('warning', __('pricing.cancel_title').' — '.__('pricing.cancel_body'));
        }
    }

    public function render()
    {
        return $this->dashboardView('livewire.dashboard.pricing', [
            'plans' => $this->getPlans(),
            'payments' => $this->getPayments(),
            'guestCount' => $this->getGuestCount(),
            'currentPlanLabel' => $this->getCurrentPlanLabel(),
            'referralDiscountPercent' => $this->getReferralDiscountPercent(),
            'hasReferralDiscount' => $this->hasReferralDiscount(),
        ], __('pricing.page_title'), [
            ['label' => __('pricing.nav_label'), 'url' => null],
        ]);
    }

    public function getReferralDiscountPercent(): ?int
    {
        return auth()->user()?->referralBuyerDiscountPercent();
    }

    public function hasReferralDiscount(): bool
    {
        return $this->getReferralDiscountPercent() !== null;
    }

    /**
     * @return list<array{
     *     tier: PlanTier,
     *     product_id: string,
     *     price: int,
     *     discounted_price: ?int,
     *     currency: string,
     *     guest_limit: ?int,
     *     highlighted: bool,
     *     purchasable: bool,
     *     reason: ?string,
     *     cta: string
     * }>
     */
    public function getPlans(): array
    {
        $user = auth()->user();
        $wedding = $user?->weddingEvent;
        $region = $user->pricingRegion();
        $plans = DodoCatalog::plansForRegion($region);
        $discountPercent = $this->getReferralDiscountPercent();

        return array_map(function (array $plan) use ($wedding, $discountPercent): array {
            $tier = $plan['tier'];
            $purchasable = $wedding?->canPurchaseTier($tier) ?? false;
            $reason = null;
            $cta = __('pricing.cta_buy');
            $price = (int) $plan['price'];
            $discountedPrice = $discountPercent !== null
                ? (int) round($price * (100 - $discountPercent) / 100)
                : null;

            if ($wedding === null) {
                $purchasable = false;
                $reason = __('pricing.error_no_wedding');
            } elseif ($wedding->plan_tier === $tier) {
                $purchasable = false;
                $cta = __('pricing.cta_current');
                $reason = __('pricing.cta_current');
            } elseif ($wedding->plan_tier !== null && $tier->sortOrder() <= $wedding->plan_tier->sortOrder()) {
                $purchasable = false;
                $cta = __('pricing.cta_lower');
                $reason = __('pricing.cta_lower');
            } elseif (! $tier->coversGuestCount($wedding->activeGuestCount())) {
                $purchasable = false;
                $cta = __('pricing.cta_too_small');
                $reason = __('pricing.cta_too_small');
            } elseif ($wedding->hasPaidPlan()) {
                $cta = __('pricing.cta_upgrade');
            }

            return [
                ...$plan,
                'discounted_price' => $discountedPrice,
                'purchasable' => $purchasable,
                'reason' => $reason,
                'cta' => $cta,
            ];
        }, $plans);
    }

    public function getGuestCount(): int
    {
        return auth()->user()?->weddingEvent?->activeGuestCount() ?? 0;
    }

    public function getCurrentPlanLabel(): string
    {
        $tier = auth()->user()?->weddingEvent?->plan_tier;

        return $tier?->label() ?? __('pricing.tier_free_name');
    }

    /**
     * @return Collection<int, DodoPayment>
     */
    public function getPayments(): Collection
    {
        $user = auth()->user();

        if ($user === null) {
            return collect();
        }

        return DodoPayment::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();
    }

    public function checkoutUrl(PlanTier $tier): string
    {
        return route('dodo.checkout', ['tier' => $tier->value]);
    }
}

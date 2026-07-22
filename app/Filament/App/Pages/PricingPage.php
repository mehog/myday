<?php

namespace App\Filament\App\Pages;

use App\Models\DodoPayment;
use App\PlanTier;
use App\Support\DodoCatalog;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class PricingPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static ?string $slug = 'pricing';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.app.pages.pricing-page';

    public static function getNavigationLabel(): string
    {
        return __('pricing.nav_label');
    }

    public function getTitle(): string
    {
        return __('pricing.page_title');
    }

    public function mount(): void
    {
        $checkout = request()->query('checkout');

        if ($checkout === 'return') {
            Notification::make()
                ->title(__('pricing.return_pending_title'))
                ->body(__('pricing.return_pending_body'))
                ->success()
                ->persistent()
                ->send();
        }

        if ($checkout === 'cancel') {
            Notification::make()
                ->title(__('pricing.cancel_title'))
                ->body(__('pricing.cancel_body'))
                ->warning()
                ->send();
        }

        if (session('error')) {
            Notification::make()
                ->title(session('error'))
                ->danger()
                ->send();
        }
    }

    /**
     * @return list<array{
     *     tier: PlanTier,
     *     product_id: string,
     *     price: int,
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

        return array_map(function (array $plan) use ($wedding): array {
            $tier = $plan['tier'];
            $purchasable = $wedding?->canPurchaseTier($tier) ?? false;
            $reason = null;
            $cta = __('pricing.cta_buy');

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
            } elseif ($wedding->plan_tier !== null) {
                $cta = __('pricing.cta_upgrade');
            }

            return [
                ...$plan,
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

        return $tier?->label() ?? __('pricing.no_plan');
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

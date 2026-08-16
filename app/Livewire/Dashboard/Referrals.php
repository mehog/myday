<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\Referral;
use App\Models\ReferralPayout;
use Illuminate\Support\Collection;
use Livewire\Component;

class Referrals extends Component
{
    use RendersDashboard;

    public ?string $paypal_email = null;

    public ?string $bank_account_info = null;

    public ?string $flashMessage = null;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user !== null && ! $user->hasReferralAccount()) {
            $user->createReferralAccount();
        }

        $this->paypal_email = $user?->paypal_email;
        $this->bank_account_info = $user?->bank_account_info;
    }

    public function render()
    {
        return $this->dashboardView('livewire.dashboard.referrals', [
            'referralLink' => $this->getReferralLink(),
            'feePercentage' => $this->getReferralFeePercentage(),
            'referrals' => $this->getReferrals(),
            'payouts' => $this->getPayouts(),
        ], __('referrals.page_title'), [
            ['label' => __('referrals.nav_label'), 'url' => null],
        ]);
    }

    public function getReferralLink(): string
    {
        return auth()->user()?->getReferralLink() ?? '';
    }

    public function getReferralFeePercentage(): float
    {
        return auth()->user()?->referralFeePercentage() ?? (float) config('referral.default_fee', 10);
    }

    /**
     * @return Collection<int, Referral>
     */
    public function getReferrals(): Collection
    {
        $userId = auth()->id();

        if ($userId === null) {
            return collect();
        }

        return Referral::query()
            ->with(['user.weddingEvent'])
            ->where('referrer_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return Collection<int, ReferralPayout>
     */
    public function getPayouts(): Collection
    {
        $user = auth()->user();

        if ($user === null) {
            return collect();
        }

        return $user->referralPayouts()
            ->orderByDesc('created_at')
            ->get();
    }

    public function savePayoutInfo(): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $data = $this->validate([
            'paypal_email' => ['nullable', 'email', 'max:255'],
            'bank_account_info' => ['nullable', 'string', 'max:2000'],
        ]);

        $user->update([
            'paypal_email' => $data['paypal_email'] ?: null,
            'bank_account_info' => $data['bank_account_info'] ?: null,
        ]);

        $this->flashMessage = __('referrals.payout_details_saved');
    }
}

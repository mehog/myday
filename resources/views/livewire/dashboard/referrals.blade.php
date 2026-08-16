@php
    use App\Support\Referral as ReferralSupport;

    $referralCookieDays = ReferralSupport::cookieExpiryDays();
    $buyerDiscountPercent = ReferralSupport::buyerDiscountPercent();
    $controlClass = 'block w-full rounded-md border border-border bg-background px-3 py-2 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">
            {{ $flashMessage }}
        </div>
    @endif

    <x-dashboard.card>
        <h2 class="text-base font-semibold">{{ __('referrals.how_it_works_heading') }}</h2>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['title' => __('referrals.step_1_title'), 'desc' => __('referrals.step_1_desc')],
                ['title' => __('referrals.step_2_title'), 'desc' => __('referrals.step_2_desc', ['days' => $referralCookieDays])],
                ['title' => __('referrals.step_3_title', ['fee' => number_format($feePercentage, 0)]), 'desc' => __('referrals.step_3_desc')],
                ['title' => __('referrals.step_4_title'), 'desc' => __('referrals.step_4_desc')],
            ] as $index => $step)
                <div class="rounded-lg border border-border bg-background p-4">
                    <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                        {{ $index + 1 }}
                    </div>
                    <h3 class="text-sm font-semibold">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </x-dashboard.card>

    <x-dashboard.card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-muted-foreground">{{ __('referrals.your_link_label') }}</p>
                <p class="mt-1 break-all font-mono text-sm">{{ $referralLink }}</p>
                <p class="mt-4 text-sm text-muted-foreground">
                    {{ __('referrals.link_help', ['days' => $referralCookieDays, 'percent' => $buyerDiscountPercent]) }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-md border border-border bg-background px-2.5 py-1 text-xs font-medium">
                    {{ __('referrals.fee_badge', ['fee' => number_format($feePercentage, 0)]) }}
                </span>
                <span class="inline-flex items-center rounded-md border border-border bg-background px-2.5 py-1 text-xs font-medium">
                    {{ __('referrals.buyer_discount_badge', ['percent' => $buyerDiscountPercent]) }}
                </span>
            </div>
        </div>

        @if ($referralLink !== '')
            <div class="mt-4 flex flex-wrap gap-2" x-data="{ copied: false }">
                <x-dashboard.button
                    type="button"
                    variant="secondary"
                    x-on:click="
                        navigator.clipboard.writeText(@js($referralLink));
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    "
                >
                    <span x-text="copied ? @js(__('referrals.link_copied')) : @js(__('referrals.copy_link'))"></span>
                </x-dashboard.button>
                <x-dashboard.button variant="secondary" href="{{ route('referrals.qr-code.download', ['format' => 'a4']) }}" target="_blank">
                    {{ __('referrals.qr_format_a4') }}
                </x-dashboard.button>
                <x-dashboard.button variant="secondary" href="{{ route('referrals.qr-code.download', ['format' => 'a5']) }}" target="_blank">
                    {{ __('referrals.qr_format_a5') }}
                </x-dashboard.button>
                <x-dashboard.button variant="secondary" href="{{ route('referrals.qr-code.download', ['format' => 'letter']) }}" target="_blank">
                    {{ __('referrals.qr_format_letter') }}
                </x-dashboard.button>
                <x-dashboard.button variant="ghost" href="{{ route('referrals.brochure.download') }}" target="_blank">
                    {{ __('referrals.download_brochure') }}
                </x-dashboard.button>
            </div>
        @endif
    </x-dashboard.card>

    <x-dashboard.card>
        <h2 class="text-base font-semibold">{{ __('referrals.my_referrals_heading') }}</h2>
        @if ($referrals->isEmpty())
            <p class="mt-3 text-sm text-muted-foreground">{{ __('referrals.referrals_empty_desc') }}</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="py-2 pr-4">{{ __('referrals.col_name') }}</th>
                            <th class="py-2 pr-4">{{ __('referrals.col_email') }}</th>
                            <th class="py-2 pr-4">{{ __('referrals.col_wedding') }}</th>
                            <th class="py-2 pr-4">{{ __('referrals.col_status') }}</th>
                            <th class="py-2">{{ __('referrals.col_referred_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($referrals as $referral)
                            @php
                                $wedding = $referral->user?->weddingEvent;
                                $status = match (true) {
                                    $wedding === null => __('referrals.status_no_wedding'),
                                    $wedding->is_active => __('referrals.status_active'),
                                    default => __('referrals.status_pending_payment'),
                                };
                            @endphp
                            <tr class="border-t border-border">
                                <td class="py-2 pr-4">{{ $referral->user?->name }}</td>
                                <td class="py-2 pr-4">{{ $referral->user?->email }}</td>
                                <td class="py-2 pr-4">{{ $wedding?->couple_names ?? __('referrals.no_wedding') }}</td>
                                <td class="py-2 pr-4">{{ $status }}</td>
                                <td class="py-2">{{ $referral->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-dashboard.card>

    <x-dashboard.card>
        <h2 class="text-base font-semibold">{{ __('referrals.payouts_heading') }}</h2>
        @if ($payouts->isEmpty())
            <p class="mt-3 text-sm text-muted-foreground">{{ __('referrals.payouts_empty_desc') }}</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="py-2 pr-4">{{ __('referrals.col_period') }}</th>
                            <th class="py-2 pr-4">{{ __('referrals.col_amount') }}</th>
                            <th class="py-2 pr-4">{{ __('referrals.col_payout_status') }}</th>
                            <th class="py-2 pr-4">{{ __('referrals.col_paid_at') }}</th>
                            <th class="py-2 pr-4">{{ __('referrals.col_payment_proof') }}</th>
                            <th class="py-2">{{ __('referrals.col_payment_link') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payouts as $payout)
                            <tr class="border-t border-border">
                                <td class="py-2 pr-4">{{ $payout->period }}</td>
                                <td class="py-2 pr-4">{{ $payout->amount }} {{ $payout->currency }}</td>
                                <td class="py-2 pr-4">{{ $payout->status?->label() }}</td>
                                <td class="py-2 pr-4">{{ $payout->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="py-2 pr-4">
                                    @if ($payout->paymentProofUrl())
                                        <a href="{{ $payout->paymentProofUrl() }}" class="text-primary underline" target="_blank" rel="noopener noreferrer">{{ __('referrals.view_proof') }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-2">
                                    @if ($payout->payment_link)
                                        <a href="{{ $payout->payment_link }}" class="text-primary underline" target="_blank" rel="noopener noreferrer">{{ __('referrals.open_link') }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-dashboard.card>

    <x-dashboard.card>
        <h2 class="text-base font-semibold">{{ __('referrals.payout_details_heading') }}</h2>
        <p class="mt-2 text-sm text-muted-foreground">{{ __('referrals.payout_details_help') }}</p>

        <form wire:submit="savePayoutInfo" class="mt-6 space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('referrals.paypal_email') }}</label>
                <input type="email" wire:model="paypal_email" class="{{ $controlClass }} h-10">
                @error('paypal_email') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('referrals.bank_account_info') }}</label>
                <textarea wire:model="bank_account_info" rows="4" class="{{ $controlClass }}"></textarea>
                <p class="mt-1 text-xs text-muted-foreground">{{ __('referrals.bank_account_helper') }}</p>
                @error('bank_account_info') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>
            <x-dashboard.button type="submit">{{ __('referrals.payout_details_save') }}</x-dashboard.button>
        </form>
    </x-dashboard.card>
</div>

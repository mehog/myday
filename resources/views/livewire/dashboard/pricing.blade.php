<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">
            {{ session('success') }}
        </div>
    @endif
    @if (session('warning'))
        <div class="rounded-lg border border-amber-300/50 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
            {{ session('warning') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-red-300/50 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100">
            {{ session('error') }}
        </div>
    @endif

    <x-dashboard.card>
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-border bg-background p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                    {{ __('pricing.current_guests', ['count' => $guestCount]) }}
                </p>
            </div>
            <div class="rounded-lg border border-border bg-background p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                    {{ __('pricing.current_plan', ['plan' => $currentPlanLabel]) }}
                </p>
            </div>
        </div>
    </x-dashboard.card>

    @if ($hasReferralDiscount)
        <div class="rounded-xl border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">
            {{ __('pricing.referral_discount_applied', ['percent' => $referralDiscountPercent]) }}
        </div>
    @endif

    <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($plans as $plan)
            @php($tier = $plan['tier'])
            <x-dashboard.card @class([
                'relative flex flex-col !border-primary' => $plan['highlighted'],
            ])>
                @if ($plan['highlighted'])
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground">
                        {{ __('pricing.popular') }}
                    </span>
                @endif

                <h3 class="text-lg font-semibold text-foreground">{{ $tier->label() }}</h3>
                <p class="mt-1 text-sm text-muted-foreground">{{ $tier->guestsLabel() }}</p>

                @if ($plan['discounted_price'] !== null)
                    <p class="mt-4 text-sm text-muted-foreground line-through">{{ $plan['price'] }} {{ $plan['currency'] }}</p>
                    <p class="text-3xl font-bold text-primary">{{ $plan['discounted_price'] }} {{ $plan['currency'] }}</p>
                @else
                    <p class="mt-4 text-3xl font-bold text-primary">{{ $plan['price'] }} {{ $plan['currency'] }}</p>
                @endif

                <div class="mt-6">
                    @if ($plan['purchasable'])
                        <form method="POST" action="{{ $this->checkoutUrl($tier) }}">
                            @csrf
                            <input type="hidden" name="tier" value="{{ $tier->value }}">
                            <x-dashboard.button type="submit" class="w-full">{{ $plan['cta'] }}</x-dashboard.button>
                        </form>
                    @else
                        <x-dashboard.button type="button" variant="secondary" class="w-full" disabled title="{{ $plan['reason'] }}">
                            {{ $plan['cta'] }}
                        </x-dashboard.button>
                    @endif
                </div>
            </x-dashboard.card>
        @endforeach
    </section>

    <x-dashboard.card>
        <p class="text-sm text-muted-foreground">{{ __('pricing.checkout_mor_note') }}</p>
        <p class="mt-3 text-sm text-muted-foreground">
            {{ __('pricing.checkout_policies_prefix') }}
            <a href="{{ route('legal.terms') }}" class="font-medium text-primary underline" target="_blank" rel="noopener noreferrer">{{ __('legal.footer_terms') }}</a>
            {{ __('pricing.checkout_policies_and') }}
            <a href="{{ route('legal.refund') }}" class="font-medium text-primary underline" target="_blank" rel="noopener noreferrer">{{ __('legal.footer_refund') }}</a>.
            {{ __('pricing.checkout_faq_prefix') }}
            <a href="{{ route('legal.faq') }}" class="font-medium text-primary underline" target="_blank" rel="noopener noreferrer">{{ __('legal.footer_faq') }}</a>.
        </p>
    </x-dashboard.card>

    <x-dashboard.card>
        <h2 class="text-base font-semibold">{{ __('pricing.features_title') }}</h2>
        <p class="mt-2 text-sm text-muted-foreground">{{ __('pricing.features_paid_note') }}</p>
        <ul class="mt-4 grid gap-2 sm:grid-cols-2">
            @foreach (range(1, 5) as $i)
                <li class="text-sm text-muted-foreground">• {{ __('pricing.feature_'.$i) }}</li>
            @endforeach
        </ul>
        <p class="mt-4 text-sm text-muted-foreground">{{ __('pricing.features_free_note') }}</p>
    </x-dashboard.card>

    <x-dashboard.card>
        <h2 class="text-base font-semibold">{{ __('pricing.payment_history') }}</h2>
        @if ($payments->isEmpty())
            <p class="mt-3 text-sm text-muted-foreground">{{ __('pricing.payment_empty') }}</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="py-2 pr-4">{{ __('pricing.nav_label') }}</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Amount</th>
                            <th class="py-2">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr class="border-t border-border">
                                <td class="py-2 pr-4">{{ $payment->plan_tier?->label() }}</td>
                                <td class="py-2 pr-4">{{ $payment->status?->label() }}</td>
                                <td class="py-2 pr-4">{{ $payment->amount }} {{ $payment->currency }}</td>
                                <td class="py-2">{{ ($payment->paid_at ?? $payment->created_at)?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-dashboard.card>
</div>

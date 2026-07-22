<x-filament-panels::page>
    <div class="space-y-8">
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pricing.current_guests', ['count' => $this->getGuestCount()]) }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('pricing.current_plan', ['plan' => $this->getCurrentPlanLabel()]) }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($this->getPlans() as $plan)
                @php($tier = $plan['tier'])
                <div @class([
                    'relative flex flex-col rounded-xl border p-6 shadow-sm',
                    'border-primary-500 bg-primary-50/40 dark:bg-primary-500/10' => $plan['highlighted'],
                    'border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900' => ! $plan['highlighted'],
                ])>
                    @if ($plan['highlighted'])
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-primary-600 px-3 py-1 text-xs font-semibold text-white">
                            {{ __('pricing.popular') }}
                        </span>
                    @endif

                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                        {{ $tier->label() }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ $tier->guestsLabel() }}
                    </p>
                    <p class="mt-4 text-3xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $plan['price'] }} {{ $plan['currency'] }}
                    </p>

                    <div class="mt-6">
                        @if ($plan['purchasable'])
                            <form method="POST" action="{{ $this->checkoutUrl($tier) }}">
                                @csrf
                                <input type="hidden" name="tier" value="{{ $tier->value }}">
                                <button
                                    type="submit"
                                    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 dark:bg-custom-500 dark:hover:bg-custom-400 w-full"
                                    style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                >
                                    {{ $plan['cta'] }}
                                </button>
                            </form>
                        @else
                            <button
                                type="button"
                                disabled
                                class="w-full rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-500 dark:bg-white/10 dark:text-gray-400"
                                title="{{ $plan['reason'] }}"
                            >
                                {{ $plan['cta'] }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                {{ __('pricing.features_title') }}
            </h2>
            <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach (range(1, 5) as $i)
                    <li class="text-sm text-gray-600 dark:text-gray-300">
                        • {{ __('pricing.feature_'.$i) }}
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                {{ __('pricing.payment_history') }}
            </h2>

            @php($payments = $this->getPayments())
            @if ($payments->isEmpty())
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                    {{ __('pricing.payment_empty') }}
                </p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">{{ __('pricing.nav_label') }}</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Amount</th>
                                <th class="py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr class="border-t border-gray-100 dark:border-white/10">
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
        </section>
    </div>
</x-filament-panels::page>

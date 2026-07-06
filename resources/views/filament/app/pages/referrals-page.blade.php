<x-filament-panels::page>
    <div class="space-y-8">
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                {{ __('referrals.how_it_works_heading') }}
            </h2>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['title' => __('referrals.step_1_title'), 'desc' => __('referrals.step_1_desc')],
                    ['title' => __('referrals.step_2_title'), 'desc' => __('referrals.step_2_desc')],
                    ['title' => __('referrals.step_3_title', ['fee' => number_format($this->getReferralFeePercentage(), 0)]), 'desc' => __('referrals.step_3_desc')],
                    ['title' => __('referrals.step_4_title'), 'desc' => __('referrals.step_4_desc')],
                ] as $index => $step)
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-white/5 dark:bg-white/5">
                        <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-full bg-primary-500/10 text-sm font-semibold text-primary-600 dark:text-primary-400">
                            {{ $index + 1 }}
                        </div>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $step['title'] }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                            {{ $step['desc'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ __('referrals.your_link_label') }}
                    </p>
                    <p class="mt-1 break-all font-mono text-sm text-gray-950 dark:text-white">
                        {{ $this->getReferralLink() }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-filament::badge color="success">
                        {{ __('referrals.fee_badge', ['fee' => number_format($this->getReferralFeePercentage(), 0)]) }}
                    </x-filament::badge>
                    <x-filament::badge color="warning">
                        {{ __('referrals.buyer_discount_badge') }}
                    </x-filament::badge>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                {{ __('referrals.link_help') }}
            </p>
        </section>

        <div class="space-y-8">
            @livewire(\App\Filament\App\Widgets\MyReferralsWidget::class)
            @livewire(\App\Filament\App\Widgets\ReferralPayoutsWidget::class)
        </div>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                {{ __('referrals.payout_details_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                {{ __('referrals.payout_details_help') }}
            </p>

            <form wire:submit="savePayoutInfo" class="mt-6">
                {{ $this->payoutForm }}

                <div class="mt-4">
                    <x-filament::button type="submit">
                        {{ __('referrals.payout_details_save') }}
                    </x-filament::button>
                </div>
            </form>
        </section>
    </div>
</x-filament-panels::page>

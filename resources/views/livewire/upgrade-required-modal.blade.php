<div>
    @if ($show)
        @teleport('body')
            <div
                x-data
                x-on:keydown.escape.window="$wire.close()"
                class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="upgrade-required-title"
                style="z-index: 9999;"
            >
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    wire:click="close"
                ></div>

                <div
                    class="relative z-10 mx-auto w-full max-w-md overflow-hidden rounded-2xl border border-primary-500/20 bg-white shadow-xl dark:border-white/10 dark:bg-gray-900"
                    style="max-width: 28rem;"
                    @click.stop
                >
                    <div class="h-1.5 bg-primary-600"></div>

                    <button
                        type="button"
                        class="absolute right-3 top-3 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white"
                        wire:click="close"
                        aria-label="{{ __('pricing.upgrade_modal_close') }}"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="px-6 pb-6 pt-7 sm:px-8">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </div>

                        <h2
                            id="upgrade-required-title"
                            class="text-center text-xl font-semibold text-gray-950 dark:text-white"
                        >
                            {{ __('pricing.upgrade_modal_title') }}
                        </h2>
                        <p class="mt-2 text-center text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                            {{ __('pricing.upgrade_modal_body') }}
                        </p>

                        <ul class="mt-5 space-y-2">
                            @foreach ([
                                'upgrade_modal_perk_qr',
                                'upgrade_modal_perk_push',
                                'upgrade_modal_perk_pdf',
                            ] as $perk)
                                <li class="flex items-center gap-3 rounded-xl bg-gray-50 px-3.5 py-2.5 text-sm text-gray-800 dark:bg-white/5 dark:text-gray-200">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-500/20 dark:text-primary-400">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </span>
                                    {{ __("pricing.{$perk}") }}
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-6 space-y-2">
                            <a
                                href="{{ $this->pricingUrl() }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white hover:bg-primary-500"
                            >
                                {{ __('pricing.upgrade_modal_cta') }}
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                            <button
                                type="button"
                                class="w-full rounded-xl px-5 py-2.5 text-sm font-medium text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5"
                                wire:click="close"
                            >
                                {{ __('pricing.upgrade_modal_cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endteleport
    @endif
</div>

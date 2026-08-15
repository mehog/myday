<div
    @if ($show)
        x-data
        x-on:keydown.escape.window="$wire.close()"
    @endif
>
    @if ($show)
        <div
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="upgrade-required-title"
        >
            <div
                class="absolute inset-0 bg-black/50"
                wire:click="close"
            ></div>

            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl sm:p-8">
                <button
                    type="button"
                    class="absolute right-4 top-4 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                    wire:click="close"
                    aria-label="{{ __('pricing.upgrade_modal_close') }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-[#c9a227]/15 text-[#c9a227]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>

                <h2 id="upgrade-required-title" class="mb-2 text-xl font-semibold text-[#1a1208]">
                    {{ __('pricing.upgrade_modal_title') }}
                </h2>
                <p class="mb-6 text-sm leading-relaxed text-[#5c5246]">
                    {{ __('pricing.upgrade_modal_body') }}
                </p>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="rounded-xl px-5 py-2.5 text-sm font-medium text-[#5c5246] hover:bg-gray-100"
                        wire:click="close"
                    >
                        {{ __('pricing.upgrade_modal_cancel') }}
                    </button>
                    <a
                        href="{{ $this->pricingUrl() }}"
                        class="inline-flex items-center justify-center rounded-xl bg-[#c9a227] px-5 py-2.5 text-sm font-semibold text-[#1a1208] hover:bg-[#b8911f]"
                    >
                        {{ __('pricing.upgrade_modal_cta') }}
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="min-h-screen flex flex-col">
    <header class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-md border-b border-[#1a1208]/10">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between max-w-lg mx-auto">
                @if ($canGoBack)
                    <button
                        type="button"
                        wire:click="previousStep"
                        class="text-[#5c5246] hover:text-[#1a1208] p-1"
                        aria-label="{{ __('onboarding.back') }}"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                @else
                    <div class="w-6"></div>
                @endif

                <a href="{{ route('home') }}" class="inline-flex items-center">
                    <img
                        src="{{ asset('icons/nd-logo-transparent.webp') }}"
                        alt="{{ config('app.name', 'NasDan') }}"
                        class="h-8 w-auto"
                        width="100"
                        height="32"
                        style="max-width: 44px;"
                    >
                </a>

                <div class="text-sm landing-body text-[#5c5246] tabular-nums min-w-[3rem] text-right">
                    {{ $countedPosition }}/{{ $countedTotal }}
                </div>
            </div>
        </div>
        <div class="w-full bg-[#1a1208]/10 h-1 overflow-hidden">
            <div
                class="bg-[#c9a227] h-1 rounded-full transition-all duration-300 ease-out"
                style="width: {{ $progressPercent }}%"
            ></div>
        </div>
    </header>

    <main class="flex-1 min-w-0 px-4 pt-24 pb-10 overflow-x-hidden">
        <div class="max-w-md mx-auto min-w-0 landing-fade-in" wire:key="step-{{ $step }}">
            @if ($submitError)
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    {{ $submitError }}
                </div>
            @endif

            @if ($previewError)
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="alert">
                    {{ $previewError }}
                </div>
            @endif

            @include('livewire.onboarding.steps.'.$step)

            @if ($isOptional && ! $isTip)
                <div class="mt-4 text-center">
                    <button
                        type="button"
                        wire:click="skipStep"
                        class="text-sm text-[#5c5246] hover:text-[#c9a227] underline-offset-2 hover:underline"
                    >
                        {{ __('onboarding.skip') }}
                    </button>
                </div>
            @endif
        </div>
    </main>

    <x-onboarding-footer />
</div>

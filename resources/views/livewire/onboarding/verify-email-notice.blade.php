<div class="min-h-screen flex flex-col">
    <header class="border-b border-white/5 bg-[#1a1208]/80 backdrop-blur-md">
        <div class="max-w-2xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="landing-heading text-xl font-semibold text-[#faf6ee]">
                {{ config('app.name', 'NasDan') }}
            </a>
            <div class="flex items-center gap-1 text-sm">
                <button
                    type="button"
                    wire:click="switchLocale('bs')"
                    class="px-2 py-1 rounded transition {{ app()->getLocale() === 'bs' ? 'text-[#c9a227] font-semibold' : 'text-[#d4c4a8] hover:text-[#c9a227]' }}"
                >
                    BS
                </button>
                <span class="text-[#d4c4a8]/50">|</span>
                <button
                    type="button"
                    wire:click="switchLocale('en')"
                    class="px-2 py-1 rounded transition {{ app()->getLocale() === 'en' ? 'text-[#c9a227] font-semibold' : 'text-[#d4c4a8] hover:text-[#c9a227]' }}"
                >
                    EN
                </button>
            </div>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-6 py-10">
        <div class="max-w-md w-full text-center landing-fade-in">
            <div class="w-16 h-16 rounded-full bg-[#c9a227]/20 flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            <h1 class="landing-heading text-3xl font-semibold text-[#faf6ee] mb-4">
                {{ __('onboarding.verify_title') }}
            </h1>

            <p class="landing-body text-[#d4c4a8] mb-8">
                {{ __('onboarding.verify_subtitle', ['email' => auth()->user()->email]) }}
            </p>

            @if ($resent || session('status') === 'verification-link-sent')
                <p class="text-sm text-[#c9a227] mb-6">
                    {{ __('onboarding.verify_sent') }}
                </p>
            @endif

            <div class="space-y-3">
                <button
                    type="button"
                    wire:click="resend"
                    wire:loading.attr="disabled"
                    class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="resend">{{ __('onboarding.verify_resend') }}</span>
                    <span wire:loading wire:target="resend">{{ __('onboarding.verify_resending') }}</span>
                </button>

                <button
                    type="button"
                    wire:click="logout"
                    class="w-full landing-btn-secondary py-4 rounded-xl landing-heading text-lg transition"
                >
                    {{ __('onboarding.verify_logout') }}
                </button>
            </div>
        </div>
    </main>
</div>

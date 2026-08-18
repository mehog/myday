<section class="landing-section px-6 py-20">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-10 landing-fade-in">
            <h2 class="landing-heading text-3xl sm:text-4xl text-[#1a1208]">
                {{ __('start.faq_title') }}
            </h2>
        </div>

        <div class="space-y-3" x-data="{ open: 1 }">
            @foreach (range(1, 6) as $i)
                <div class="landing-card rounded-2xl border border-[#1a1208]/10 bg-white overflow-hidden landing-fade-in">
                    <button
                        type="button"
                        class="w-full text-left px-6 py-5 flex items-start justify-between gap-4"
                        @click="open = open === {{ $i }} ? 0 : {{ $i }}"
                        :aria-expanded="open === {{ $i }}"
                    >
                        <span class="landing-heading text-lg text-[#1a1208]">{{ __('start.faq_q'.$i) }}</span>
                        <span class="landing-heading text-[#c9a227] text-xl leading-none shrink-0" x-text="open === {{ $i }} ? '−' : '+'"></span>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak class="px-6 pb-5">
                        <p class="landing-body text-sm text-[#5c5246] leading-relaxed">
                            {{ __('start.faq_a'.$i, ['days' => config('legal.refund_window_days')]) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

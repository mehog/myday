<section class="landing-section px-6 py-16 sm:py-20 bg-[#fafaf8]">
    <div class="max-w-3xl mx-auto text-center landing-fade-in">
        <h2 class="landing-heading text-3xl sm:text-4xl text-[#1a1208] mb-4">
            {{ __('start.guarantee_title') }}
        </h2>
        <p class="landing-body text-lg text-[#5c5246] leading-relaxed mb-6">
            {{ __('start.guarantee_text', ['days' => config('legal.refund_window_days')]) }}
        </p>
        <a
            href="{{ route('legal.refund') }}"
            class="text-sm text-[#c9a227] hover:underline"
        >
            {{ __('start.guarantee_link') }}
        </a>
    </div>
</section>

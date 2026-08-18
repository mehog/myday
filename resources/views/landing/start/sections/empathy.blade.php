<section class="landing-section px-6 py-16 sm:py-20 bg-[#fafaf8]">
    <div class="max-w-3xl mx-auto text-center landing-fade-in">
        <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">
            {{ __('start.empathy_eyebrow') }}
        </p>
        <x-landing.headline
            :lead="__('start.empathy_title_lead')"
            :emphasis="__('start.empathy_title_emphasis')"
            class="text-3xl sm:text-4xl text-[#1a1208] mb-6"
        />
        <p class="landing-body text-lg text-[#5c5246] leading-relaxed">
            {{ __('start.empathy_text') }}
        </p>
    </div>
</section>

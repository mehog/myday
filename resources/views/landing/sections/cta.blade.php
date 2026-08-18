<section class="landing-cta-band px-6 py-20">
    <div class="max-w-3xl mx-auto text-center landing-fade-in">
        <x-landing.headline
            tag="h2"
            :lead="__('landing.cta_title_lead')"
            :emphasis="__('landing.cta_title_emphasis')"
            class="text-3xl sm:text-4xl md:text-5xl mb-4"
        />
        <p class="landing-body text-lg mb-10">
            {{ __('landing.cta_subtitle') }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a
                href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}"
                class="landing-btn-primary px-8 py-4 rounded-full landing-heading text-lg transition inline-flex items-center justify-center"
            >
                {{ __('landing.hero_cta_create') }}
            </a>
            <a
                href="#demo"
                class="landing-btn-secondary px-8 py-4 rounded-full landing-heading text-lg transition inline-flex items-center justify-center"
            >
                {{ __('landing.hero_cta_demo') }}
            </a>
        </div>
    </div>
</section>

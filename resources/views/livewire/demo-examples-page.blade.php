<div>
    <section class="landing-section px-6 py-16 sm:py-20">
        <div class="max-w-6xl mx-auto">
            <div class="mb-10 landing-fade-in text-center sm:text-left">
                <a
                    href="{{ route('home') }}#demo"
                    class="inline-block landing-body text-sm text-[#c9a227] hover:text-[#faf6ee] transition mb-4"
                >
                    ← {{ __('landing.demo_gallery_back') }}
                </a>
                <h1 class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-4">
                    {{ __('landing.demo_gallery_title') }}
                </h1>
                <p class="landing-body text-[#d4c4a8] max-w-2xl">
                    {{ __('landing.demo_gallery_subtitle') }}
                </p>
            </div>

            @include('landing.partials.demo-grid', ['examples' => $examples])
        </div>
    </section>

    @include('landing.sections.footer')
</div>

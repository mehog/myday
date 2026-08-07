<section
    id="demo"
    class="landing-section py-20 scroll-mt-20"
>
    <div class="max-w-6xl mx-auto">
        <div class="px-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-10 landing-fade-in">
            <div class="text-center sm:text-left max-w-2xl">
                <h2 class="landing-heading text-3xl sm:text-4xl text-[#1a1208] mb-4">
                    {{ __('landing.demo_title') }}
                </h2>
                <p class="landing-body text-[#5c5246]">
                    {{ __('landing.demo_subtitle') }}
                </p>
            </div>

            <a
                href="{{ route('demo.examples') }}"
                class="landing-btn-secondary shrink-0 self-center sm:self-auto px-6 py-3 rounded-xl landing-heading text-base transition"
            >
                {{ __('landing.demo_show_all') }}
            </a>
        </div>

        <div class="sm:px-6">
            @include('landing.partials.demo-slider', ['examples' => $demos])
        </div>
    </div>
</section>

@props([
    'examples' => [],
])

<div
    class="landing-demo-slider-wrap"
    x-data="{
        scrollByDir(dir) {
            const track = this.$refs.track;
            if (! track) return;
            const slide = track.querySelector('.landing-demo-slide');
            const amount = slide ? slide.offsetWidth + 24 : 300;
            track.scrollBy({ left: dir * amount, behavior: 'smooth' });
        }
    }"
>
    <div class="relative">
        <button
            type="button"
            class="landing-demo-slider-nav landing-demo-slider-nav--prev"
            @click="scrollByDir(-1)"
            aria-label="{{ __('landing.demo_slider_prev') }}"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        <div
            x-ref="track"
            class="landing-demo-slider"
            role="list"
        >
            @foreach ($examples as $example)
                <div class="landing-demo-slide" role="listitem">
                    @include('landing.partials.demo-card', ['example' => $example, 'lazy' => false])
                </div>
            @endforeach
        </div>

        <button
            type="button"
            class="landing-demo-slider-nav landing-demo-slider-nav--next"
            @click="scrollByDir(1)"
            aria-label="{{ __('landing.demo_slider_next') }}"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</div>

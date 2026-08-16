@props([
    'example',
    'lazy' => false,
])

<div class="landing-demo-card" x-data>
    <div class="landing-demo-phone-wrap">
        <div class="landing-phone-frame mx-auto max-w-[280px] sm:max-w-[320px] landing-demo-phone">
            <iframe
                src="{{ $example['previewUrl'] }}"
                title="{{ $example['title'] }}"
                class="landing-demo-iframe"
                loading="{{ $lazy ? 'lazy' : 'eager' }}"
                tabindex="-1"
            ></iframe>
        </div>
        <button
            type="button"
            class="landing-demo-slide-hit"
            aria-label="{{ $example['title'] }}"
            x-on:click="$dispatch('invitation-preview-open', { url: @js($example['openUrl']), title: @js($example['title']) })"
        ></button>
    </div>
    <p class="landing-heading landing-demo-slide-title">
        <button
            type="button"
            class="landing-demo-slide-title-link"
            x-on:click="$dispatch('invitation-preview-open', { url: @js($example['openUrl']), title: @js($example['title']) })"
        >
            {{ $example['title'] }}
        </button>
    </p>
</div>

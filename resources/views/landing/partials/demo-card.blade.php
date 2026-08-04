@props([
    'example',
    'lazy' => false,
])

<div class="landing-demo-card">
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
        <a
            href="{{ $example['openUrl'] }}"
            target="_blank"
            rel="noopener noreferrer"
            class="landing-demo-slide-hit"
            aria-label="{{ $example['title'] }}"
        ></a>
    </div>
    <p class="landing-heading landing-demo-slide-title">
        <a
            href="{{ $example['openUrl'] }}"
            target="_blank"
            rel="noopener noreferrer"
            class="landing-demo-slide-title-link"
        >
            {{ $example['title'] }}
        </a>
    </p>
</div>

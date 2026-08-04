@props([
    'examples' => [],
])

<div class="landing-demo-grid" role="list">
    @foreach ($examples as $example)
        <div class="landing-demo-grid-item" role="listitem">
            @include('landing.partials.demo-card', ['example' => $example, 'lazy' => $loop->index >= 6])
        </div>
    @endforeach
</div>

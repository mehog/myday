<div>
    @include('landing.sections.hero')
    @include('landing.sections.value-strip')
    @include('landing.sections.benefits')
    @include('landing.sections.stories')
    @include('landing.sections.guest-interaction')
    @include('landing.sections.how-it-works')
    @include('landing.sections.demo', ['demos' => $demos])
    @include('landing.sections.pricing')
    @include('landing.sections.cta')
    @include('landing.sections.footer')
</div>

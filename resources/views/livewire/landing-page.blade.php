<div>
    @include('landing.sections.hero')
    @include('landing.sections.benefits')
    @include('landing.sections.how-it-works')
    @include('landing.sections.demo', ['demos' => $demos])
    @include('landing.sections.contact')
    @include('landing.sections.footer')
</div>

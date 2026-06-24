<header class="landing-nav fixed top-0 inset-x-0 z-50 border-b border-white/5 bg-[#1a1208]/80 backdrop-blur-md">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="inline-flex items-center">
            <img
                src="{{ asset('icons/nd-logo-transparent.webp') }}"
                alt="{{ config('app.name', 'NasDan') }}"
                class="h-9 w-auto"
                width="120"
                height="36"
                style="max-width: 50px;"
            >
        </a>
        <nav class="flex items-center gap-4 sm:gap-6">
            <a href="#demo" class="text-sm text-[#d4c4a8] hover:text-[#c9a227] transition hidden sm:inline">
                {{ __('landing.nav_demo') }}
            </a>
            <a href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}" class="text-sm text-[#d4c4a8] hover:text-[#c9a227] transition hidden sm:inline">
                {{ __('landing.nav_create') }}
            </a>
            <a href="#naruči" class="text-sm px-4 py-2 rounded-full border border-[#c9a227] text-[#c9a227] hover:bg-[#c9a227] hover:text-[#1a1208] transition">
                {{ __('landing.nav_order') }}
            </a>
        </nav>
    </div>
</header>

<section class="landing-hero min-h-screen flex items-center justify-center px-6 pt-24 pb-16">
    <div class="max-w-3xl mx-auto text-center landing-fade-in">
        <h1 class="landing-heading text-4xl sm:text-5xl md:text-6xl font-semibold text-[#faf6ee] leading-tight mb-6">
            {{ __('landing.hero_title') }}
        </h1>
        <p class="landing-body text-lg sm:text-xl text-[#d4c4a8] max-w-2xl mx-auto mb-10">
            {{ __('landing.hero_subtitle') }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a
                href="#demo"
                class="landing-btn-primary px-8 py-4 rounded-xl landing-heading text-lg transition"
            >
                {{ __('landing.hero_cta_demo') }}
            </a>
            <a
                href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}"
                class="landing-btn-secondary px-8 py-4 rounded-xl landing-heading text-lg transition"
            >
                {{ __('landing.hero_cta_create') }}
            </a>
            <a
                href="#naruči"
                class="landing-btn-secondary px-8 py-4 rounded-xl landing-heading text-lg transition hidden sm:inline-flex items-center justify-center"
            >
                {{ __('landing.hero_cta_order') }}
            </a>
        </div>
    </div>
</section>

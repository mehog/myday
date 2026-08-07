<header class="landing-nav fixed top-0 inset-x-0 z-50 border-b border-[#1a1208]/10 bg-white/80 backdrop-blur-md">
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
            <a href="#features" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition hidden sm:inline">
                {{ __('landing.nav_features') }}
            </a>
            <a href="#cijene" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition hidden sm:inline">
                {{ __('landing.nav_pricing') }}
            </a>
            <a href="#demo" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition hidden sm:inline">
                {{ __('landing.nav_demo') }}
            </a>
            @guest
                <a href="/app/login" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition inline">
                    {{ __('landing.nav_login') }}
                </a>
            @else
                <a href="/app" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition sm:inline">
                    {{ __('landing.nav_panel') }}
                </a>
            @endguest
            <a href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}" class="text-sm px-4 py-2 rounded-full border border-[#c9a227] text-[#c9a227] hover:bg-[#c9a227] hover:text-[#1a1208] transition">
                {{ __('landing.nav_create') }}
            </a>
        </nav>
    </div>
</header>

<section class="landing-hero min-h-[100svh] flex items-center px-6 pt-28 pb-16 overflow-hidden">
    <div class="max-w-6xl mx-auto w-full grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
        <div class="landing-fade-in text-center lg:text-left">
            <h1 class="landing-heading text-4xl sm:text-5xl md:text-[3.25rem] font-semibold text-[#1a1208] leading-tight mb-6">
                {{ __('landing.hero_title') }}
            </h1>
            <p class="landing-body text-lg sm:text-xl text-[#5c5246] max-w-xl mx-auto lg:mx-0 mb-10">
                {{ __('landing.hero_subtitle') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a
                    href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}"
                    class="landing-btn-primary px-8 py-4 rounded-xl landing-heading text-lg transition inline-flex items-center justify-center"
                >
                    {{ __('landing.hero_cta_create') }}
                </a>
                <a
                    href="#demo"
                    class="landing-btn-secondary px-8 py-4 rounded-xl landing-heading text-lg transition inline-flex items-center justify-center"
                >
                    {{ __('landing.hero_cta_demo') }}
                </a>
            </div>
        </div>

        <div class="landing-hero-visual landing-fade-in relative mx-auto w-full max-w-lg lg:max-w-none">
            <div class="landing-browser-frame landing-hero-dashboard" aria-hidden="true">
                <div class="landing-browser-chrome">
                    <span></span><span></span><span></span>
                </div>
                    <img
                    src="{{ asset(\App\Support\LandingAsset::path('hero-dashboard-desktop.webp')) }}"
                    alt=""
                    width="1600"
                    height="1000"
                    class="w-full h-auto"
                    fetchpriority="high"
                >
            </div>
            <div class="landing-phone-frame landing-hero-phone">
                <img
                    src="{{ asset(\App\Support\LandingAsset::path('hero-invitation-mobile.webp')) }}"
                    alt="{{ __('landing.hero_image_alt') }}"
                    width="390"
                    height="844"
                    class="w-full h-auto"
                    fetchpriority="high"
                >
            </div>
        </div>
    </div>
</section>

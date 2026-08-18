<header class="landing-nav fixed top-0 inset-x-0 z-50 border-b border-[#1a1208]/10 bg-white/80 backdrop-blur-md">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('start') }}" class="inline-flex items-center">
            <img
                src="{{ asset('icons/nd-logo-transparent.webp') }}"
                alt="{{ config('app.name') }}"
                class="h-9 w-auto"
                width="120"
                height="36"
                style="max-width: 50px;"
            >
        </a>
        <nav class="flex items-center gap-4 sm:gap-6">
            <a href="#how-it-works" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition hidden sm:inline">
                {{ __('start.nav_how') }}
            </a>
            <a href="#pricing" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition hidden sm:inline">
                {{ __('start.nav_pricing') }}
            </a>
            <a href="#demo" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition hidden sm:inline">
                {{ __('start.nav_demo') }}
            </a>
            @guest
                <a href="/app/login" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition inline">
                    {{ __('start.nav_login') }}
                </a>
            @else
                <a href="/app" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition sm:inline">
                    {{ __('start.nav_panel') }}
                </a>
            @endguest
            <a href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}" class="text-sm px-4 py-2 rounded-full bg-[#c9a227] text-[#1a1208] hover:bg-[#a8841a] transition">
                {{ __('start.nav_create') }}
            </a>
        </nav>
    </div>
</header>

<section class="landing-hero min-h-[100svh] flex items-center px-6 pt-28 pb-16 overflow-hidden">
    <div class="max-w-6xl mx-auto w-full grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
        <div class="landing-fade-in text-center lg:text-left">
            <p class="landing-label text-xs uppercase text-[#c9a227] mb-4">
                {{ __('start.hero_eyebrow') }}
            </p>
            <x-landing.headline
                tag="h1"
                :lead="__('start.hero_title_lead')"
                :emphasis="__('start.hero_title_emphasis')"
                :tail="__('start.hero_title_tail')"
                class="text-4xl sm:text-5xl md:text-[3.4rem] leading-[1.12] text-[#1a1208] mb-6"
            />
            <p class="landing-body text-lg sm:text-xl text-[#5c5246] max-w-xl mx-auto lg:mx-0 mb-10">
                {{ __('start.hero_subtitle') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a
                    href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}"
                    class="landing-btn-primary px-8 py-4 rounded-full landing-heading text-lg transition inline-flex items-center justify-center"
                >
                    {{ __('start.hero_cta_create') }}
                </a>
                <a
                    href="#demo"
                    class="landing-btn-secondary px-8 py-4 rounded-full landing-heading text-lg transition inline-flex items-center justify-center"
                >
                    {{ __('start.hero_cta_demo') }}
                </a>
            </div>
            <div class="landing-trust mt-8">
                <span class="landing-trust-item landing-body">{{ __('start.trust_free') }}</span>
                <span class="landing-trust-item landing-body">{{ __('start.trust_ready') }}</span>
                <span class="landing-trust-item landing-body">{{ __('start.trust_payment') }}</span>
            </div>
        </div>

        <div class="landing-fade-in">
            @include('landing.partials.invitation-mock')
        </div>
    </div>
</section>

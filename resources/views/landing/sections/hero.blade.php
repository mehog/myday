<header class="landing-nav fixed top-0 inset-x-0 z-50 border-b border-white/10 backdrop-blur-md">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="landing-heading text-2xl font-semibold text-[#faf6ee]">
            {{ config('app.name', 'NasDan') }}
        </a>
        <nav class="flex items-center gap-6">
            <a href="#demo" class="text-sm text-[#d4c4a8] hover:text-[#c9a227] transition hidden sm:inline">
                {{ __('landing.nav_demo') }}
            </a>
            <a href="#naruči" class="text-sm px-4 py-2 rounded-full border border-[#c9a227] text-[#c9a227] hover:bg-[#c9a227] hover:text-[#1a1208] transition">
                {{ __('landing.nav_order') }}
            </a>
        </nav>
    </div>
</header>

<section class="landing-hero min-h-screen flex items-center justify-center px-6 pt-24 pb-16">
    <div class="landing-hero-content max-w-3xl mx-auto text-center landing-fade-in rounded-3xl px-8 py-12 sm:px-12 sm:py-14">
        <p class="text-sm uppercase tracking-[0.35em] text-[#e8dcc8] mb-6">
            {{ config('app.name', 'NasDan') }}
        </p>
        <h1 class="landing-hero-title landing-heading text-4xl sm:text-5xl md:text-6xl font-semibold text-[#faf6ee] leading-tight mb-6">
            {{ __('landing.hero_title') }}
        </h1>
        <p class="landing-body text-lg sm:text-xl text-[#e8dcc8] max-w-2xl mx-auto mb-10">
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
                href="#naruči"
                class="landing-btn-secondary px-8 py-4 rounded-xl landing-heading text-lg transition"
            >
                {{ __('landing.hero_cta_order') }}
            </a>
        </div>
    </div>
</section>

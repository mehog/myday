@props([
    'heading',
    'legal',
])

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
            <a href="{{ route('home') }}" class="text-sm text-[#d4c4a8] hover:text-[#c9a227] transition">
                {{ __('legal.nav_home') }}
            </a>
            @guest
                <a href="/app/login" class="text-sm text-[#d4c4a8] hover:text-[#c9a227] transition">
                    {{ __('landing.nav_login') }}
                </a>
            @else
                <a href="/app" class="text-sm text-[#d4c4a8] hover:text-[#c9a227] transition">
                    {{ __('landing.nav_panel') }}
                </a>
            @endguest
        </nav>
    </div>
</header>

<main class="pt-24 pb-16">
    <article class="landing-section px-6 py-12">
        <div class="max-w-3xl mx-auto landing-fade-in">
            <p class="landing-label text-xs text-[#c9a227] uppercase mb-3">
                {{ __('legal.effective_date', ['date' => $legal['effective_date']]) }}
            </p>
            <h1 class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-6">
                {{ $heading }}
            </h1>

            <div class="legal-content landing-body text-[#d4c4a8] space-y-6 leading-relaxed [&_h2]:landing-heading [&_h2]:text-xl [&_h2]:text-[#faf6ee] [&_h2]:mt-10 [&_h2]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-2 [&_a]:text-[#c9a227] [&_a]:hover:underline [&_strong]:text-[#faf6ee]">
                {{ $slot }}
            </div>
        </div>
    </article>
</main>

@include('landing.sections.footer')

@extends('layouts.landing')

@section('content')
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
        @php($referralCookieDays = \App\Support\Referral::cookieExpiryDays())

        <section class="landing-section px-6 py-12">
            <div class="max-w-4xl mx-auto text-center landing-fade-in">
                <h1 class="landing-heading text-3xl sm:text-4xl md:text-5xl text-[#faf6ee] mb-4">
                    {{ __('referrals.page_title') }}
                </h1>
                <p class="landing-body text-lg text-[#d4c4a8] max-w-2xl mx-auto">
                    {{ __('referrals.page_subheading', ['fee' => number_format($fee, 0)]) }}
                </p>
            </div>
        </section>

        <section class="landing-section px-6 py-12">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-10 landing-fade-in">
                    <h2 class="landing-heading text-2xl sm:text-3xl text-[#faf6ee]">
                        {{ __('referrals.how_it_works_heading') }}
                    </h2>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['title' => __('referrals.step_1_title'), 'desc' => __('referrals.step_1_desc')],
                        ['title' => __('referrals.step_2_title'), 'desc' => __('referrals.step_2_desc', ['days' => $referralCookieDays])],
                        ['title' => __('referrals.step_3_title', ['fee' => number_format($fee, 0)]), 'desc' => __('referrals.step_3_desc')],
                        ['title' => __('referrals.step_4_title'), 'desc' => __('referrals.step_4_desc')],
                    ] as $index => $step)
                        <div class="landing-card rounded-2xl border border-white/15 p-6 landing-fade-in">
                            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#c9a227] bg-[#1a1208] text-sm font-semibold text-[#c9a227] landing-heading">
                                {{ $index + 1 }}
                            </div>
                            <h3 class="landing-heading text-lg text-[#faf6ee] mb-2">
                                {{ $step['title'] }}
                            </h3>
                            <p class="landing-body text-sm text-[#d4c4a8] leading-relaxed">
                                {{ $step['desc'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="landing-section px-6 py-12">
            <div class="max-w-3xl mx-auto landing-card rounded-2xl border border-white/15 p-8 text-center landing-fade-in">
                <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
                    <span class="inline-flex items-center rounded-full border border-[#c9a227]/40 bg-[#c9a227]/10 px-4 py-1.5 text-sm font-medium text-[#c9a227]">
                        {{ __('referrals.fee_badge', ['fee' => number_format($fee, 0)]) }}
                    </span>
                    <span class="inline-flex items-center rounded-full border border-amber-500/40 bg-amber-500/10 px-4 py-1.5 text-sm font-medium text-amber-300">
                        {{ __('referrals.buyer_discount_badge') }}
                    </span>
                </div>
                <p class="landing-body text-[#d4c4a8] leading-relaxed">
                    {{ __('referrals.public_link_help', ['days' => $referralCookieDays]) }}
                </p>
            </div>
        </section>

        <section class="landing-section px-6 py-12">
            <div class="max-w-3xl mx-auto text-center landing-fade-in">
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a
                        href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}"
                        class="landing-btn-primary px-8 py-4 rounded-xl landing-heading text-lg transition"
                    >
                        {{ __('referrals.public_cta') }}
                    </a>
                    @guest
                        <a
                            href="/app/login"
                            class="landing-btn-secondary px-8 py-4 rounded-xl landing-heading text-lg transition"
                        >
                            {{ __('landing.nav_login') }}
                        </a>
                    @else
                        <a
                            href="{{ route('filament.app.pages.referrals') }}"
                            class="landing-btn-secondary px-8 py-4 rounded-xl landing-heading text-lg transition"
                        >
                            {{ __('referrals.nav_label') }}
                        </a>
                    @endguest
                </div>
            </div>
        </section>
    </main>

    @include('landing.sections.footer')
@endsection

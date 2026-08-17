@extends('layouts.landing')

@section('content')
    <header class="landing-nav fixed top-0 inset-x-0 z-50 border-b border-[#1a1208]/10 bg-white/80 backdrop-blur-md">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="inline-flex items-center">
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
                <a href="{{ route('home') }}" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition hidden sm:inline">
                    {{ __('packages.nav_home') }}
                </a>
                <a href="{{ route('demo.examples') }}" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition hidden sm:inline">
                    {{ __('packages.nav_demo') }}
                </a>
                @guest
                    <a href="/app/login" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition">
                        {{ __('landing.nav_login') }}
                    </a>
                @else
                    <a href="/app" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition">
                        {{ __('landing.nav_panel') }}
                    </a>
                @endguest
                <a
                    href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}"
                    class="text-sm px-4 py-2 rounded-full border border-[#c9a227] text-[#c9a227] hover:bg-[#c9a227] hover:text-[#1a1208] transition"
                >
                    {{ __('landing.nav_create') }}
                </a>
            </nav>
        </div>
    </header>

    <main class="pt-24 pb-16">
        <section class="landing-section px-6 py-12">
            <div class="max-w-4xl mx-auto text-center landing-fade-in">
                <h1 class="landing-heading text-3xl sm:text-4xl md:text-5xl text-[#1a1208] mb-4">
                    {{ __('packages.index.heading') }}
                </h1>
                <p class="landing-body text-lg text-[#5c5246] max-w-2xl mx-auto">
                    {{ __('packages.index.subheading') }}
                </p>
            </div>
        </section>

        <section class="landing-section px-6 py-12 bg-[#fafaf8]">
            <div class="max-w-5xl mx-auto">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($plans as $plan)
                        <article @class([
                            'landing-card rounded-2xl p-6 sm:p-8 landing-fade-in relative flex flex-col',
                            'border-2 border-[#c9a227]/60 bg-[#c9a227]/5' => $plan['highlighted'],
                            'border border-[#1a1208]/15 bg-white' => ! $plan['highlighted'],
                        ])>
                            @if ($plan['highlighted'])
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-[#c9a227] text-[#1a1208] text-xs font-medium uppercase tracking-wider text-center">
                                    {{ __('landing.pricing_plan_popular') }}
                                </span>
                            @endif

                            <div class="text-center mb-6">
                                <h2 class="landing-heading text-2xl text-[#1a1208] mb-2">{{ $plan['name'] }}</h2>
                                <p class="landing-body text-sm text-[#5c5246] mb-4">{{ $plan['guests_label'] }}</p>
                                <p class="landing-heading text-4xl text-[#1a1208]">{{ $plan['price_label'] }}</p>
                                <p class="landing-body text-xs text-[#5c5246]/80 mt-2">{{ __('landing.pricing_one_time') }}</p>
                            </div>

                            <p class="landing-body text-sm text-[#5c5246] leading-relaxed mb-6 flex-1">
                                {{ __('packages.tiers.'.$plan['tier']->value.'.best_for') }}
                            </p>

                            <a
                                href="{{ $plan['url'] }}"
                                @class([
                                    'w-full py-3 rounded-xl landing-heading text-center transition mb-3',
                                    'landing-btn-primary' => $plan['highlighted'],
                                    'landing-btn-secondary' => ! $plan['highlighted'],
                                ])
                            >
                                {{ __('packages.index.view_details') }}
                            </a>
                            <a
                                href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}"
                                class="text-center text-sm text-[#c9a227] hover:underline"
                            >
                                {{ __('packages.shared.create_cta') }}
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="landing-section px-6 py-16">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-10 landing-fade-in">
                    <h2 class="landing-heading text-2xl sm:text-3xl text-[#1a1208] mb-3">
                        {{ __('packages.index.compare_heading') }}
                    </h2>
                    <p class="landing-body text-[#5c5246]">
                        {{ __('packages.index.compare_subheading') }}
                    </p>
                </div>

                <div class="overflow-x-auto landing-fade-in">
                    <table class="w-full min-w-[640px] text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#1a1208]/15">
                                <th class="landing-heading py-3 pr-4 text-[#1a1208] font-medium">{{ __('packages.compare.package') }}</th>
                                <th class="landing-heading py-3 px-4 text-[#1a1208] font-medium">{{ __('packages.compare.guests') }}</th>
                                <th class="landing-heading py-3 px-4 text-[#1a1208] font-medium">{{ __('packages.compare.price') }}</th>
                                <th class="landing-heading py-3 pl-4 text-[#1a1208] font-medium">{{ __('packages.compare.best_for') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($plans as $plan)
                                <tr class="border-b border-[#1a1208]/10">
                                    <td class="py-4 pr-4">
                                        <a href="{{ $plan['url'] }}" class="landing-heading text-[#c9a227] hover:underline">
                                            {{ $plan['name'] }}
                                        </a>
                                    </td>
                                    <td class="landing-body py-4 px-4 text-[#5c5246]">{{ $plan['guests_label'] }}</td>
                                    <td class="landing-body py-4 px-4 text-[#5c5246]">{{ $plan['price_label'] }}</td>
                                    <td class="landing-body py-4 pl-4 text-[#5c5246]">
                                        {{ __('packages.tiers.'.$plan['tier']->value.'.best_for') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="landing-body text-sm text-[#5c5246] mt-8 text-center landing-fade-in">
                    {{ __('packages.shared.all_features_note') }}
                </p>
                <p class="landing-body text-sm text-[#5c5246] mt-3 text-center landing-fade-in">
                    {{ __('landing.pricing_free_limitations_note') }}
                </p>
            </div>
        </section>

        <section class="landing-section px-6 py-12 bg-[#fafaf8]">
            <div class="max-w-3xl mx-auto text-center landing-fade-in">
                <h2 class="landing-heading text-2xl sm:text-3xl text-[#1a1208] mb-4">
                    {{ __('packages.index.features_heading') }}
                </h2>
                <ul class="grid sm:grid-cols-2 gap-x-10 gap-y-4 text-left mt-8 mb-6">
                    @foreach (range(1, 14) as $i)
                        <li class="landing-body text-sm text-[#5c5246] leading-relaxed pl-4 border-l border-[#c9a227]/40">
                            {{ __('landing.pricing_feature_'.$i) }}
                        </li>
                    @endforeach
                </ul>
                <p class="landing-body text-sm text-[#5c5246] mb-10 text-left">
                    {{ __('landing.pricing_free_limitations_note') }}
                </p>
                <a
                    href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}"
                    class="landing-btn-primary inline-block px-8 py-4 rounded-xl landing-heading text-lg transition"
                >
                    {{ __('packages.shared.create_cta') }}
                </a>
            </div>
        </section>
    </main>

    @include('landing.sections.footer')
@endsection

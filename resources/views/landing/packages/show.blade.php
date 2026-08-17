@extends('layouts.landing')

@section('content')
    @php
        /** @var \App\PlanTier $tier */
        $tier = $plan['tier'];
    @endphp

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
                <a href="{{ route('packages.index') }}" class="text-sm text-[#5c5246] hover:text-[#c9a227] transition hidden sm:inline">
                    {{ __('packages.nav_packages') }}
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
            <div class="max-w-4xl mx-auto landing-fade-in">
                <p class="mb-4">
                    <a href="{{ route('packages.index') }}" class="text-sm text-[#c9a227] hover:underline">
                        &larr; {{ __('packages.shared.back_to_packages') }}
                    </a>
                </p>
                <h1 class="landing-heading text-3xl sm:text-4xl md:text-5xl text-[#1a1208] mb-4">
                    {{ __('packages.tiers.'.$tier->value.'.heading') }}
                </h1>
                <p class="landing-body text-lg text-[#5c5246] max-w-2xl mb-8">
                    {{ __('packages.tiers.'.$tier->value.'.summary') }}
                </p>
                <div class="flex flex-wrap items-end gap-6 mb-8">
                    <div>
                        <p class="landing-heading text-5xl text-[#1a1208]">{{ $plan['price_label'] }}</p>
                        <p class="landing-body text-sm text-[#5c5246] mt-1">{{ __('landing.pricing_one_time') }}</p>
                    </div>
                    <p class="landing-body text-[#5c5246]">{{ $plan['guests_label'] }}</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a
                        href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}"
                        class="landing-btn-primary px-8 py-4 rounded-xl landing-heading text-lg transition text-center"
                    >
                        {{ __('packages.shared.create_cta') }}
                    </a>
                    <a
                        href="{{ route('demo.examples') }}"
                        class="landing-btn-secondary px-8 py-4 rounded-xl landing-heading text-lg transition text-center"
                    >
                        {{ __('packages.shared.demo_cta') }}
                    </a>
                </div>
            </div>
        </section>

        <section class="landing-section px-6 py-12 bg-[#fafaf8]">
            <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-10">
                <div class="landing-fade-in">
                    <h2 class="landing-heading text-2xl text-[#1a1208] mb-3">{{ __('packages.shared.best_for') }}</h2>
                    <p class="landing-body text-[#5c5246] leading-relaxed">
                        {{ __('packages.tiers.'.$tier->value.'.best_for') }}
                    </p>
                </div>
                <div class="landing-fade-in">
                    <h2 class="landing-heading text-2xl text-[#1a1208] mb-3">{{ __('packages.shared.limitations') }}</h2>
                    <p class="landing-body text-[#5c5246] leading-relaxed">
                        {{ __('packages.tiers.'.$tier->value.'.limitation') }}
                    </p>
                </div>
            </div>
        </section>

        <section class="landing-section px-6 py-16">
            <div class="max-w-4xl mx-auto">
                <div class="mb-8 landing-fade-in">
                    <h2 class="landing-heading text-2xl sm:text-3xl text-[#1a1208] mb-3">
                        {{ __('packages.shared.includes') }}
                    </h2>
                    <p class="landing-body text-[#5c5246]">
                        {{ __('packages.shared.all_features_note') }}
                    </p>
                    @if ($tier === \App\PlanTier::Free)
                        <p class="landing-body text-sm text-[#5c5246] mt-3">
                            {{ __('landing.pricing_free_limitations_note') }}
                        </p>
                    @endif
                </div>
                <ul class="grid sm:grid-cols-2 gap-x-10 gap-y-4 landing-fade-in">
                    @foreach (range(1, 14) as $i)
                        <li class="landing-body text-sm text-[#5c5246] leading-relaxed pl-4 border-l border-[#c9a227]/40">
                            {{ __('landing.pricing_feature_'.$i) }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="landing-section px-6 py-12 bg-[#fafaf8]">
            <div class="max-w-4xl mx-auto">
                <h2 class="landing-heading text-2xl sm:text-3xl text-[#1a1208] mb-8 text-center landing-fade-in">
                    {{ __('packages.shared.compare_heading') }}
                </h2>
                <div class="overflow-x-auto landing-fade-in">
                    <table class="w-full min-w-[520px] text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#1a1208]/15">
                                <th class="landing-heading py-3 pr-4 text-[#1a1208] font-medium">{{ __('packages.compare.package') }}</th>
                                <th class="landing-heading py-3 px-4 text-[#1a1208] font-medium">{{ __('packages.compare.guests') }}</th>
                                <th class="landing-heading py-3 pl-4 text-[#1a1208] font-medium">{{ __('packages.compare.price') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($plans as $other)
                                <tr @class([
                                    'border-b border-[#1a1208]/10',
                                    'bg-[#c9a227]/10' => $other['tier'] === $tier,
                                ])>
                                    <td class="py-4 pr-4">
                                        @if ($other['tier'] === $tier)
                                            <span class="landing-heading text-[#1a1208]">{{ $other['name'] }}</span>
                                        @else
                                            <a href="{{ $other['url'] }}" class="landing-heading text-[#c9a227] hover:underline">
                                                {{ $other['name'] }}
                                            </a>
                                        @endif
                                    </td>
                                    <td class="landing-body py-4 px-4 text-[#5c5246]">{{ $other['guests_label'] }}</td>
                                    <td class="landing-body py-4 pl-4 text-[#5c5246]">{{ $other['price_label'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="landing-section px-6 py-16">
            <div class="max-w-3xl mx-auto">
                <h2 class="landing-heading text-2xl sm:text-3xl text-[#1a1208] mb-8 landing-fade-in">
                    {{ __('packages.shared.faq_heading') }}
                </h2>
                <div class="space-y-8">
                    @foreach (range(1, 4) as $i)
                        <div class="landing-fade-in">
                            <h3 class="landing-heading text-lg text-[#1a1208] mb-2">
                                {{ __('packages.tiers.'.$tier->value.'.faq_'.$i.'_q') }}
                            </h3>
                            <p class="landing-body text-[#5c5246] leading-relaxed">
                                {{ __('packages.tiers.'.$tier->value.'.faq_'.$i.'_a') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="landing-section px-6 py-12 bg-[#fafaf8]">
            <div class="max-w-3xl mx-auto text-center landing-fade-in">
                <h2 class="landing-heading text-2xl sm:text-3xl text-[#1a1208] mb-4">
                    {{ __('packages.tiers.'.$tier->value.'.cta_heading') }}
                </h2>
                <p class="landing-body text-[#5c5246] mb-8">
                    {{ __('packages.shared.cta_subheading') }}
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

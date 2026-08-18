<footer class="landing-section px-6 py-14 border-t border-[#1a1208]/10">
    <div class="max-w-6xl mx-auto">
        <div class="grid gap-10 md:grid-cols-3 mb-10">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center mb-4">
                    <img
                        src="{{ asset('icons/nd-logo-transparent.webp') }}"
                        alt="{{ config('app.name') }}"
                        class="h-8 w-auto"
                        width="120"
                        height="36"
                        style="max-width: 50px;"
                    >
                </a>
                <p class="landing-heading text-xl text-[#1a1208] mb-2">{{ config('app.name') }}</p>
                <p class="landing-body text-sm text-[#5c5246] max-w-xs">
                    {{ __('landing.footer_tagline') }}
                </p>
            </div>
            <div>
                <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">{{ __('landing.footer_nav_heading') }}</p>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('home') }}" class="text-[#5c5246] hover:text-[#c9a227] transition">{{ __('landing.nav_features') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('packages.index') }}" class="text-[#5c5246] hover:text-[#c9a227] transition">{{ __('landing.footer_packages') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('referral-program') }}" class="text-[#5c5246] hover:text-[#c9a227] transition">{{ __('landing.footer_referral_program') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('legal.faq') }}" class="text-[#5c5246] hover:text-[#c9a227] transition">{{ __('legal.footer_faq') }}</a>
                    </li>
                </ul>
            </div>
            <div>
                <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">{{ __('landing.footer_legal_heading') }}</p>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('legal.terms') }}" class="text-[#5c5246] hover:text-[#c9a227] transition">{{ __('legal.footer_terms') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('legal.privacy') }}" class="text-[#5c5246] hover:text-[#c9a227] transition">{{ __('legal.footer_privacy') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('legal.refund') }}" class="text-[#5c5246] hover:text-[#c9a227] transition">{{ __('legal.footer_refund') }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4 text-sm text-[#5c5246] pt-6 border-t border-[#1a1208]/10">
            <p class="text-center md:text-left">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('landing.footer_rights') }}</p>
            <p class="text-center">{{ __('landing.footer_contact') }}: <a href="mailto:{{ config('legal.support_email') }}" class="text-[#c9a227] hover:underline">{{ config('legal.support_email') }}</a></p>
            <div class="flex justify-center md:justify-end">
                <x-locale-picker />
            </div>
        </div>
    </div>
</footer>

<footer class="landing-section px-6 py-10 border-t border-[#1a1208]/10">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 mb-4 text-sm">
            <a href="{{ route('packages.index') }}" class="text-[#c9a227] hover:underline">
                {{ __('landing.footer_packages') }}
            </a>
            <span class="text-[#1a1208]/20 hidden sm:inline" aria-hidden="true">·</span>
            <a href="{{ route('referral-program') }}" class="text-[#c9a227] hover:underline">
                {{ __('landing.footer_referral_program') }}
            </a>
            <span class="text-[#1a1208]/20 hidden sm:inline" aria-hidden="true">·</span>
            <a href="{{ route('legal.faq') }}" class="text-[#c9a227] hover:underline">
                {{ __('legal.footer_faq') }}
            </a>
            <span class="text-[#1a1208]/20 hidden sm:inline" aria-hidden="true">·</span>
            <a href="{{ route('legal.terms') }}" class="text-[#c9a227] hover:underline">
                {{ __('legal.footer_terms') }}
            </a>
            <span class="text-[#1a1208]/20 hidden sm:inline" aria-hidden="true">·</span>
            <a href="{{ route('legal.privacy') }}" class="text-[#c9a227] hover:underline">
                {{ __('legal.footer_privacy') }}
            </a>
            <span class="text-[#1a1208]/20 hidden sm:inline" aria-hidden="true">·</span>
            <a href="{{ route('legal.refund') }}" class="text-[#c9a227] hover:underline">
                {{ __('legal.footer_refund') }}
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4 text-sm text-[#5c5246]">
        <p class="text-center md:text-left">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('landing.footer_rights') }}</p>
        <p class="text-center">{{ __('landing.footer_contact') }}: <a href="mailto:{{ config('legal.support_email') }}" class="text-[#c9a227] hover:underline">{{ config('legal.support_email') }}</a></p>
        <div class="flex justify-center md:justify-end">
            <x-locale-picker />
        </div>
        </div>
    </div>
</footer>

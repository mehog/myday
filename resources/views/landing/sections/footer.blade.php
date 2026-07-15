<footer class="landing-section px-6 py-10 border-t border-white/5">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-4">
            <a href="{{ route('referral-program') }}" class="text-[#c9a227] hover:underline">
                {{ __('landing.footer_referral_program') }}
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4 text-sm text-[#d4c4a8]">
        <p class="text-center md:text-left">&copy; {{ date('Y') }} {{ config('app.name', 'NasDan') }}. {{ __('landing.footer_rights') }}</p>
        <p class="text-center">{{ __('landing.footer_contact') }}: <a href="mailto:info@nasdan.ba" class="text-[#c9a227] hover:underline">info@nasdan.ba</a></p>
        <div class="flex justify-center md:justify-end">
            <x-locale-picker />
        </div>
        </div>
    </div>
</footer>

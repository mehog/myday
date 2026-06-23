<footer class="landing-section px-6 py-10 border-t border-white/5">
    <div class="max-w-6xl mx-auto flex flex-col gap-6 text-sm text-[#d4c4a8]">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'NasDan') }}. {{ __('landing.footer_rights') }}</p>
            <p>{{ __('landing.footer_contact') }}: <a href="mailto:info@nasdan.ba" class="text-[#c9a227] hover:underline">info@nasdan.ba</a></p>
        </div>
        <div class="flex justify-center sm:justify-end">
            <x-locale-picker />
        </div>
    </div>
</footer>

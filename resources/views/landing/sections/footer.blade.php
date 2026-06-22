<footer class="landing-section landing-section-alt px-6 py-10 border-t border-white/10">
    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-[#d4c4a8]">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'NasDan') }}. {{ __('landing.footer_rights') }}</p>
        <p>{{ __('landing.footer_contact') }}: <a href="mailto:info@nasdan.ba" class="text-[#c9a227] hover:underline">info@nasdan.ba</a></p>
    </div>
</footer>

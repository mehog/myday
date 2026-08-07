<footer class="border-t border-[#1a1208]/10 px-6 py-6 mt-auto">
    <div class="max-w-2xl mx-auto flex flex-col items-center gap-4 text-sm text-[#5c5246]">
        <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-2 text-xs sm:text-sm">
            <a href="{{ route('legal.terms') }}" class="text-[#c9a227] hover:underline">{{ __('legal.footer_terms') }}</a>
            <a href="{{ route('legal.privacy') }}" class="text-[#c9a227] hover:underline">{{ __('legal.footer_privacy') }}</a>
            <a href="{{ route('legal.refund') }}" class="text-[#c9a227] hover:underline">{{ __('legal.footer_refund') }}</a>
            <a href="{{ route('legal.faq') }}" class="text-[#c9a227] hover:underline">{{ __('legal.footer_faq') }}</a>
        </div>
        <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'NasDan') }}</p>
            <x-locale-picker />
        </div>
    </div>
</footer>

<footer class="border-t border-white/5 px-6 py-6 mt-auto">
    <div class="max-w-2xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-[#d4c4a8]">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'NasDan') }}</p>
        <x-locale-picker />
    </div>
</footer>

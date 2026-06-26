<div>
    @if ($isPreview)
        <div class="fixed top-0 inset-x-0 z-50 bg-[#c9a227] text-[#1a1208] px-4 py-3 text-sm flex items-center justify-center gap-2 shadow-md">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-center">{{ __('invitation.preview_banner') }}</p>
        </div>
    @endif

    @if ($event->is_demo && $showDemoSwitcher)
        <div @class([
            'fixed inset-x-0 z-50 bg-[#1a1208]/95 backdrop-blur border-b border-[#c9a227]/30 text-sm',
            'top-0' => ! $isPreview,
            'top-12' => $isPreview,
        ])>
            <div class="relative flex flex-wrap items-center justify-center gap-3 sm:gap-4 px-10 py-2.5">
                <select
                    wire:model.live="previewTheme"
                    class="text-sm py-1.5 px-3 min-w-[9rem] cursor-pointer rounded-xl border border-[#c9a227]/40 bg-[#2a1f0f] text-[#faf6ee]"
                    aria-label="{{ __('app.theme') }}"
                >
                    @foreach ($themes as $themeOption)
                        <option value="{{ $themeOption->value }}">{{ $themeOption->label() }}</option>
                    @endforeach
                </select>
                <select
                    wire:model.live="previewTemplate"
                    class="text-sm py-1.5 px-3 min-w-[9rem] cursor-pointer rounded-xl border border-[#c9a227]/40 bg-[#2a1f0f] text-[#faf6ee]"
                    aria-label="{{ __('app.template') }}"
                >
                    @foreach ($templates as $templateOption)
                        <option value="{{ $templateOption->value }}">{{ $templateOption->label() }}</option>
                    @endforeach
                </select>
                <button
                    type="button"
                    wire:click="$set('showDemoSwitcher', false)"
                    class="absolute top-2 right-2 p-1.5 rounded-lg text-[#d4c4a8] hover:text-[#faf6ee] hover:bg-white/10 transition"
                    aria-label="{{ __('invitation.demo_switcher_close') }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @php
        $bannerCount = ($isPreview ? 1 : 0) + ($event->is_demo && $showDemoSwitcher ? 1 : 0);
    @endphp

    <x-theme :theme="$activeTheme">
        <div @class([
            'invitation-page',
            'pt-12' => $bannerCount === 1,
            'pt-24' => $bannerCount === 2,
        ])>
            @include('components.invitation.templates.'.$activeTemplate->value, [
                'event' => $event,
                'guest' => $guest,
                'isPersonalLink' => $isPersonalLink,
            ])

            <footer class="py-8 px-6 border-t border-[color-mix(in_srgb,var(--color-text)_10%,transparent)] flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="shrink-0">
                    <img
                        src="{{ asset('icons/nd-logo-transparent.webp') }}"
                        alt="{{ config('app.name', 'NasDan') }}"
                        class="max-w-[50px] w-full h-auto"
                        style="max-width: 50px;"
                    >
                </a>
                <x-locale-picker
                    class="justify-end"
                    selectClass="text-sm py-1.5 px-3 min-w-[9rem] cursor-pointer rounded-xl border border-[color-mix(in_srgb,var(--color-primary)_40%,transparent)] bg-[var(--color-bg-soft)] text-[var(--color-text)]"
                    labelClass="text-sm text-[var(--color-text-muted)]"
                />
            </footer>
        </div>
    </x-theme>
</div>

<div>
    @if ($activeReveal)
        @include('components.invitation.reveals.'.$activeReveal->value, [
            'event' => $event,
            'isPreview' => $isPreview,
        ])
    @endif

    @if ($isPreview)
        <div class="fixed top-0 inset-x-0 z-50 bg-[#c9a227] text-[#1a1208] px-4 py-3 text-sm flex items-center justify-center gap-2 shadow-md">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-center">{{ __('invitation.preview_banner') }}</p>
        </div>
    @endif

    @if ($event->is_demo && $showDemoSwitcher)
        <style>
            @keyframes demoRibbonSlide {
                from {
                    opacity: 0;
                    transform: translateY(-100%);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes demoOptionFade {
                from {
                    opacity: 0;
                    transform: translateY(0.5rem);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>

        <div
            @class([
                'fixed inset-x-0 z-50 bg-[#1a1208]/60 backdrop-blur border-b border-[#c9a227]/30 text-sm',
                'top-0' => ! $isPreview,
                'top-12' => $isPreview,
            ])
            style="animation: demoRibbonSlide 0.35s ease both"
        >
            <div class="relative flex items-center justify-center gap-1.5 sm:gap-3 px-8 py-2 sm:py-2.5">
                <select
                    wire:model.live="previewTheme"
                    class="flex-1 min-w-0 max-w-[8rem] sm:max-w-none text-xs sm:text-sm py-1.5 px-2 sm:px-3 cursor-pointer rounded-xl border border-[#c9a227]/40 bg-[#2a1f0f] text-[#faf6ee]"
                    style="animation: demoOptionFade 0.3s ease both; animation-delay: 0.15s"
                    aria-label="{{ __('app.theme') }}"
                >
                    @foreach ($themes as $themeOption)
                        <option value="{{ $themeOption->value }}">{{ $themeOption->label() }}</option>
                    @endforeach
                </select>
                <select
                    wire:model.live="previewTemplate"
                    class="flex-1 min-w-0 max-w-[8rem] sm:max-w-none text-xs sm:text-sm py-1.5 px-2 sm:px-3 cursor-pointer rounded-xl border border-[#c9a227]/40 bg-[#2a1f0f] text-[#faf6ee]"
                    style="animation: demoOptionFade 0.3s ease both; animation-delay: 0.3s"
                    aria-label="{{ __('app.template') }}"
                >
                    @foreach ($templates as $templateOption)
                        <option value="{{ $templateOption->value }}">{{ $templateOption->label() }}</option>
                    @endforeach
                </select>
                <select
                    wire:model.live="previewReveal"
                    class="flex-1 min-w-0 max-w-[8rem] sm:max-w-none text-xs sm:text-sm py-1.5 px-2 sm:px-3 cursor-pointer rounded-xl border border-[#c9a227]/40 bg-[#2a1f0f] text-[#faf6ee]"
                    style="animation: demoOptionFade 0.3s ease both; animation-delay: 0.45s"
                    aria-label="{{ __('app.reveal_animation') }}"
                >
                    <option value="">{{ __('app.reveal_none') }}</option>
                    @foreach ($reveals as $revealOption)
                        <option value="{{ $revealOption->value }}">{{ $revealOption->label() }}</option>
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

    <div
        id="invitation-content"
        @if (! $isPreview && ! $invitationRevealed && $activeReveal)
            style="opacity:0;pointer-events:none;transition:opacity .6s ease .2s"
        @endif
    >
        <x-theme :theme="$activeTheme">
            <div @class([
                'invitation-page',
                'pt-12' => $bannerCount === 1,
                'pt-24' => $bannerCount === 2,
                'pb-20' => $showRsvpNudge,
            ])>
                @include('components.invitation.templates.'.$activeTemplate->value, [
                    'event' => $event,
                    'guest' => $guest,
                    'isPersonalLink' => $isPersonalLink,
                    'showRsvpNudge' => $showRsvpNudge,
                ])

                @include('components.invitation.rsvp-sticky-bar', [
                    'showRsvpNudge' => $showRsvpNudge,
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
</div>

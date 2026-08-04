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

    @if ($isTokenOnlyPreview)
        <div
            @class([
                'fixed inset-x-0 z-50 bg-[#2a1f0f] text-[#faf6ee] px-4 py-3 text-sm flex items-center justify-center gap-2 shadow-md border-b border-[#c9a227]/40',
                'top-0' => ! $isPreview,
                'top-12' => $isPreview,
            ])
        >
            <svg class="w-4 h-4 shrink-0 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <p class="text-center">{{ __('invitation.token_only_preview_banner') }}</p>
        </div>
    @endif

    @php
        $bannerCount = ($isPreview ? 1 : 0) + ($isTokenOnlyPreview ? 1 : 0);
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
                'pb-20' => $showRsvpNudge || $showDemoCreateNudge,
            ])>
                @include('components.invitation.templates.'.$activeTemplate->value, [
                    'event' => $event,
                    'guest' => $guest,
                    'isPersonalLink' => $isPersonalLink,
                    'showRsvpNudge' => $showRsvpNudge,
                    'visibleMenuOptions' => $visibleMenuOptions,
                ])

                @include('components.invitation.rsvp-sticky-bar', [
                    'showRsvpNudge' => $showRsvpNudge,
                    'showDemoCreateNudge' => $showDemoCreateNudge,
                    'demoCreateUrl' => $demoCreateUrl,
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

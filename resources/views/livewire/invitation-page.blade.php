<div>
    @if ($isPreview)
        <div class="fixed top-0 inset-x-0 z-50 bg-[#c9a227] text-[#1a1208] px-4 py-3 text-sm flex items-center justify-center gap-2 shadow-md">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-center">{{ __('invitation.preview_banner') }}</p>
        </div>
    @endif

    <x-theme :theme="$event->theme">
        <div @class(['invitation-page', 'pt-12' => $isPreview])>
            @include('components.invitation.hero', ['event' => $event])

            @include('components.invitation.countdown', ['event' => $event])

            @if ($event->scheduleItems->isNotEmpty())
                @include('components.invitation.schedule', ['event' => $event])
            @endif

            @if ($event->location_name || $event->location_address)
                @include('components.invitation.location', ['event' => $event])
            @endif

            @if ($event->eventPhotos->isNotEmpty())
                @include('components.invitation.gallery', ['event' => $event])
            @endif

            @if ($event->youtube_embed_url)
                @include('components.invitation.music-player', ['event' => $event])
            @endif

            @include('components.invitation.rsvp', [
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

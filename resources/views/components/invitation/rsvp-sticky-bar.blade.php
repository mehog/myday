@if ($showRsvpNudge ?? false)
    <div
        x-data="{
            rsvpVisible: false,
            init() {
                const rsvp = document.getElementById('rsvp');

                if (! rsvp) {
                    return;
                }

                const observer = new IntersectionObserver(
                    ([entry]) => { this.rsvpVisible = entry.isIntersecting; },
                    { threshold: 0.15 },
                );

                observer.observe(rsvp);
            },
        }"
        x-show="! rsvpVisible"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed inset-x-0 bottom-0 z-40 border-t border-[color-mix(in_srgb,var(--color-text)_10%,transparent)] bg-[color-mix(in_srgb,var(--color-bg)_75%,transparent)] backdrop-blur-sm px-4 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]"
        style="display: none;"
    >
        <div class="mx-auto flex max-w-xl items-center justify-between gap-3">
            <p class="invitation-body text-sm text-[var(--color-text-muted)]">
                {{ __('invitation.rsvp_nudge_sticky_text') }}
            </p>
            @include('components.invitation.rsvp-nudge-link', ['variant' => 'sticky'])
        </div>
    </div>
@endif

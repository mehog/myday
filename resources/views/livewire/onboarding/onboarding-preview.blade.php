<div>
    {{-- Play reveal as a guest would: pass isPreview=false so the stage renders. --}}
    @if ($activeReveal)
        @include('components.invitation.reveals.'.$activeReveal->value, [
            'event' => $event,
            'isPreview' => false,
        ])
    @endif

    @if ($isPreview)
        <div class="fixed top-0 inset-x-0 z-50 bg-[#c9a227] text-[#1a1208] px-4 py-3 text-sm flex items-center justify-center gap-2 shadow-md">
            <p class="text-center">{{ __('onboarding.preview_draft_banner') }}</p>
        </div>
    @endif

    <div
        id="invitation-content"
        @class(['pt-12' => $isPreview])
        @if ($activeReveal && ! $invitationRevealed)
            style="opacity:0;pointer-events:none;transition:opacity .6s ease .2s"
        @endif
    >
        <x-theme :theme="$activeTheme">
            <div @class(['invitation-page pb-8', 'pb-20' => $showRsvpNudge])>
                @include('components.invitation.templates.'.$activeTemplate->value, [
                    'event' => $event,
                    'guest' => $guest,
                    'isPersonalLink' => $isPersonalLink,
                    'showRsvpNudge' => $showRsvpNudge,
                    'visibleMenuOptions' => $visibleMenuOptions,
                    'isTokenOnlyPreview' => $isTokenOnlyPreview,
                ])

                @include('components.invitation.rsvp-sticky-bar', [
                    'showRsvpNudge' => $showRsvpNudge,
                    'showDemoCreateNudge' => $showDemoCreateNudge,
                    'demoCreateUrl' => $demoCreateUrl,
                ])
            </div>
        </x-theme>
    </div>
</div>

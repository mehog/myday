@if (! $isPreview)
    @php
        $revealDate = $event->wedding_date->format('d · m · Y');
        $revealMeta = collect([$revealDate, $event->location_name])->filter()->implode(' — ');

        $sealPalettes = [
            'amber-gold' => [
                '--seal-bg-1' => '#0d0a04',
                '--seal-bg-2' => '#1a1208',
                '--seal-accent' => '#c9a227',
                '--seal-ink' => '#faf6ee',
                '--seal-wax-1' => '#d4a574',
                '--seal-wax-2' => '#a8841a',
                '--seal-wax-3' => '#6b4f12',
            ],
            'royal-wedding' => [
                '--seal-bg-1' => '#080f1a',
                '--seal-bg-2' => '#0f1a2e',
                '--seal-accent' => '#d4af37',
                '--seal-ink' => '#f8f6f0',
                '--seal-wax-1' => '#c41e3a',
                '--seal-wax-2' => '#8b1529',
                '--seal-wax-3' => '#5c0e1b',
            ],
            'lavender-dream' => [
                '--seal-bg-1' => '#1a1220',
                '--seal-bg-2' => '#2d2438',
                '--seal-accent' => '#c9b8e0',
                '--seal-ink' => '#faf8fc',
                '--seal-wax-1' => '#c9a0d8',
                '--seal-wax-2' => '#9b7bb8',
                '--seal-wax-3' => '#6d5585',
            ],
            'winter-magic' => [
                '--seal-bg-1' => '#0d1520',
                '--seal-bg-2' => '#1a2332',
                '--seal-accent' => '#7eb8da',
                '--seal-ink' => '#f0f8ff',
                '--seal-wax-1' => '#a8d4f0',
                '--seal-wax-2' => '#5a9bc4',
                '--seal-wax-3' => '#3a6580',
            ],
            'pearl-white' => [
                '--seal-bg-1' => '#5a4535',
                '--seal-bg-2' => '#6E5A42',
                '--seal-accent' => '#8C7355',
                '--seal-ink' => '#FAFAF8',
                '--seal-wax-1' => '#c4a882',
                '--seal-wax-2' => '#8C7355',
                '--seal-wax-3' => '#5c4835',
            ],
            'dusty-rose' => [
                '--seal-bg-1' => '#7a3a35',
                '--seal-bg-2' => '#9A5A55',
                '--seal-accent' => '#E8C9C4',
                '--seal-ink' => '#F9F1EE',
                '--seal-wax-1' => '#e8958f',
                '--seal-wax-2' => '#B5706A',
                '--seal-wax-3' => '#8a4540',
            ],
        ];

        $sealPalette = $sealPalettes[$event->theme->value] ?? $sealPalettes['amber-gold'];
    @endphp

    <style>
        :root {
            @foreach ($sealPalette as $var => $val)
                {{ $var }}: {{ $val }};
            @endforeach
        }

        .seal-stage {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
            background: radial-gradient(
                120% 90% at 50% 8%,
                color-mix(in srgb, var(--seal-bg-2) 50%, white) 0%,
                color-mix(in srgb, var(--seal-bg-2) 72%, white) 42%,
                color-mix(in srgb, var(--seal-bg-1) 58%, white) 100%
            );
            transition: opacity 0.5s ease;
        }

        .seal-stage.seal-gone {
            opacity: 0;
            pointer-events: none;
        }

        .seal-flash {
            position: absolute;
            inset: 0;
            background: white;
            opacity: 0;
            pointer-events: none;
            z-index: 10;
        }

        .seal-stage.seal-opening .seal-flash {
            animation: seal-flash 0.35s ease-out 0.9s forwards;
        }

        @keyframes seal-flash {
            0% { opacity: 0; }
            40% { opacity: 0.85; }
            100% { opacity: 0; }
        }

        .seal-trigger {
            position: relative;
            width: 120px;
            height: 120px;
            cursor: pointer;
            border: none;
            background: transparent;
            padding: 0;
            animation: seal-bob 5s ease-in-out infinite;
        }

        .seal-stage.seal-opening .seal-trigger {
            animation: none;
            pointer-events: none;
        }

        @keyframes seal-bob {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .seal-wrap {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .seal-half {
            position: absolute;
            inset: 0;
            transition: transform 0.55s cubic-bezier(0.5, 0, 0.85, 0.35);
        }

        .seal-half-left {
            clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);
        }

        .seal-half-right {
            clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%);
        }

        .seal-stage.seal-opening .seal-half-left {
            transform: translate(-18px, 8px) rotate(-22deg);
            transition-delay: 0.35s;
        }

        .seal-stage.seal-opening .seal-half-right {
            transform: translate(18px, 8px) rotate(22deg);
            transition-delay: 0.35s;
        }

        .seal-stage.seal-opening .seal-trigger {
            opacity: 0;
            transition: opacity 0.3s ease 0.85s;
        }

        .seal-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, var(--seal-wax-1), var(--seal-wax-2) 55%, var(--seal-wax-3));
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35), inset 0 2px 8px rgba(255, 255, 255, 0.2);
        }

        .seal-stage.seal-opening .seal-ring {
            animation: seal-crack 0.35s ease-out forwards;
        }

        @keyframes seal-crack {
            0% { filter: brightness(1); transform: scale(1); }
            50% { filter: brightness(1.15); transform: scale(1.04); }
            100% { filter: brightness(0.95); transform: scale(1); }
        }

        .seal-heart {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 36px;
            height: 36px;
            color: rgba(255, 255, 255, 0.92);
        }

        .seal-caption {
            margin-top: 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.55);
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .seal-stage.seal-opening .seal-caption {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .seal-names {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            opacity: 0;
            pointer-events: none;
            color: var(--seal-ink);
            z-index: 5;
        }

        .seal-names .seal-eyebrow {
            display: block;
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--seal-accent);
            margin-bottom: 8px;
        }

        .seal-names .seal-couple {
            font-family: 'Playfair Display', serif;
            font-size: clamp(24px, 5vw, 36px);
            font-style: italic;
            font-weight: 600;
        }

        .seal-names .seal-meta {
            margin-top: 8px;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.75;
        }

        .seal-stage.seal-opening .seal-names {
            animation: seal-names-in 0.6s ease 1.1s forwards;
        }

        @keyframes seal-names-in {
            from { opacity: 0; transform: translate(-50%, -40%); }
            to { opacity: 1; transform: translate(-50%, -50%); }
        }

        @media (prefers-reduced-motion: reduce) {
            .seal-trigger { animation: none; }
            .seal-half { transition-duration: 0.001s; }
        }
    </style>

    <div class="seal-stage" id="seal-stage" wire:ignore>
        <div class="seal-flash" aria-hidden="true"></div>
        <div class="seal-names" aria-hidden="true">
            <span class="seal-eyebrow">{{ __('invitation.save_the_date') }}</span>
            <div class="seal-couple">{{ $event->couple_names }}</div>
            @if ($revealMeta)
                <div class="seal-meta">{{ $revealMeta }}</div>
            @endif
        </div>
        <button
            type="button"
            class="seal-trigger"
            id="seal-trigger"
            aria-label="{{ __('invitation.envelope_open') }}"
        >
            <div class="seal-wrap">
                <div class="seal-half seal-half-left">
                    <div class="seal-ring"></div>
                    <svg class="seal-heart" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 21c-5.6-3.8-9.6-6.9-9.6-11.3A4.7 4.7 0 0 1 12 6.3 4.7 4.7 0 0 1 21.6 9.7C21.6 14.1 17.6 17.2 12 21z"/>
                    </svg>
                </div>
                <div class="seal-half seal-half-right">
                    <div class="seal-ring"></div>
                    <svg class="seal-heart" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 21c-5.6-3.8-9.6-6.9-9.6-11.3A4.7 4.7 0 0 1 12 6.3 4.7 4.7 0 0 1 21.6 9.7C21.6 14.1 17.6 17.2 12 21z"/>
                    </svg>
                </div>
            </div>
        </button>
        <p class="seal-caption">{{ __('invitation.envelope_tap') }}</p>
    </div>

    <script>
        (function () {
            const trigger = document.getElementById('seal-trigger');
            const stage = document.getElementById('seal-stage');
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            let started = false;
            let revealedToLivewire = false;

            function showInviteContent() {
                const el = document.getElementById('invitation-content');
                if (!el) return;
                el.style.opacity = '1';
                el.style.pointerEvents = 'auto';
                if (revealedToLivewire) return;
                const root = el.closest('[wire\\:id]');
                if (root && window.Livewire) {
                    const component = Livewire.find(root.getAttribute('wire:id'));
                    if (component) {
                        component.set('invitationRevealed', true);
                        revealedToLivewire = true;
                    }
                }
            }

            function finishReveal() {
                showInviteContent();
                document.dispatchEvent(new CustomEvent('invitation:revealed'));
            }

            function reveal() {
                if (started) return;
                started = true;

                if (reduce) {
                    stage.classList.add('seal-gone');
                    finishReveal();
                    return;
                }

                stage.classList.add('seal-opening');
                showInviteContent();

                setTimeout(() => {
                    stage.classList.add('seal-gone');
                    finishReveal();
                }, 1600);
            }

            trigger.addEventListener('click', reveal);
        })();
    </script>
@endif

@if (! $isPreview)
    @php
        $revealDate = $event->wedding_date->format('d · m · Y');
        $revealMeta = collect([$revealDate, $event->location_name])->filter()->implode(' — ');

        $curtPalettes = [
            'amber-gold' => [
                '--curt-panel' => '#2a1f0f',
                '--curt-panel-edge' => '#3d2910',
                '--curt-accent' => '#c9a227',
                '--curt-ink' => '#faf6ee',
                '--curt-bg' => '#1a1208',
            ],
            'royal-wedding' => [
                '--curt-panel' => '#1a2744',
                '--curt-panel-edge' => '#1e3a5f',
                '--curt-accent' => '#d4af37',
                '--curt-ink' => '#f8f6f0',
                '--curt-bg' => '#0f1a2e',
            ],
            'lavender-dream' => [
                '--curt-panel' => '#3d3249',
                '--curt-panel-edge' => '#9b7bb8',
                '--curt-accent' => '#c9b8e0',
                '--curt-ink' => '#faf8fc',
                '--curt-bg' => '#2d2438',
            ],
            'winter-magic' => [
                '--curt-panel' => '#243044',
                '--curt-panel-edge' => '#7eb8da',
                '--curt-accent' => '#7eb8da',
                '--curt-ink' => '#f0f8ff',
                '--curt-bg' => '#1a2332',
            ],
            'pearl-white' => [
                '--curt-panel' => '#6E5A42',
                '--curt-panel-edge' => '#a88b6a',
                '--curt-accent' => '#8C7355',
                '--curt-ink' => '#FAFAF8',
                '--curt-bg' => '#5a4535',
            ],
            'dusty-rose' => [
                '--curt-panel' => '#9A5A55',
                '--curt-panel-edge' => '#c98580',
                '--curt-accent' => '#E8C9C4',
                '--curt-ink' => '#F9F1EE',
                '--curt-bg' => '#7a3a35',
            ],
        ];

        $curtPalette = $curtPalettes[$event->theme->value] ?? $curtPalettes['amber-gold'];
    @endphp

    <style>
        :root {
            @foreach ($curtPalette as $var => $val)
                {{ $var }}: {{ $val }};
            @endforeach
        }

        .curt-stage {
            position: fixed;
            inset: 0;
            z-index: 100;
            overflow: hidden;
            background: var(--curt-bg);
            transition: opacity 0.45s ease;
        }

        .curt-stage.curt-gone {
            opacity: 0;
            pointer-events: none;
        }

        .curt-panel {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 50vw;
            background: linear-gradient(90deg, var(--curt-panel), var(--curt-panel-edge));
            transition: transform 0.85s cubic-bezier(0.7, 0, 0.3, 1);
            box-shadow: inset 0 0 80px rgba(0, 0, 0, 0.25);
        }

        .curt-panel-left {
            left: 0;
            transform-origin: left center;
        }

        .curt-panel-right {
            right: 0;
            transform-origin: right center;
            background: linear-gradient(-90deg, var(--curt-panel), var(--curt-panel-edge));
        }

        .curt-panel-left::after,
        .curt-panel-right::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, transparent, var(--curt-accent), transparent);
            opacity: 0.5;
        }

        .curt-panel-left::after {
            right: 0;
        }

        .curt-panel-right::after {
            left: 0;
        }

        .curt-stage.curt-opening .curt-panel-left {
            transform: translateX(-100%);
        }

        .curt-stage.curt-opening .curt-panel-right {
            transform: translateX(100%);
        }

        .curt-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px;
            color: var(--curt-ink);
            font-family: 'Montserrat', sans-serif;
            cursor: pointer;
            z-index: 2;
            transition: opacity 0.3s ease;
        }

        .curt-stage.curt-opening .curt-center {
            opacity: 0;
            pointer-events: none;
        }

        .curt-eyebrow {
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--curt-accent);
            margin-bottom: 12px;
        }

        .curt-names {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 6vw, 48px);
            font-style: italic;
            font-weight: 600;
            line-height: 1.15;
        }

        .curt-meta {
            margin-top: 12px;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.75;
        }

        .curt-hint {
            position: absolute;
            bottom: 48px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.45);
        }

        .curt-stage.curt-opening .curt-hint {
            opacity: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            .curt-panel { transition-duration: 0.001s; }
        }
    </style>

    <div class="curt-stage" id="curt-stage" wire:ignore>
        <div class="curt-panel curt-panel-left" aria-hidden="true"></div>
        <div class="curt-panel curt-panel-right" aria-hidden="true"></div>
        <div
            class="curt-center"
            id="curt-trigger"
            role="button"
            tabindex="0"
            aria-label="{{ __('invitation.envelope_open') }}"
        >
            <span class="curt-eyebrow">{{ __('invitation.save_the_date') }}</span>
            <div class="curt-names">{{ $event->couple_names }}</div>
            @if ($revealMeta)
                <div class="curt-meta">{{ $revealMeta }}</div>
            @endif
        </div>
        <span class="curt-hint">{{ __('invitation.envelope_tap') }}</span>
    </div>

    <script>
        (function () {
            const trigger = document.getElementById('curt-trigger');
            const stage = document.getElementById('curt-stage');
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
                    stage.classList.add('curt-gone');
                    finishReveal();
                    return;
                }

                stage.classList.add('curt-opening');
                showInviteContent();

                setTimeout(() => {
                    stage.classList.add('curt-gone');
                    finishReveal();
                }, 950);
            }

            trigger.addEventListener('click', reveal);
            trigger.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    reveal();
                }
            });
        })();
    </script>
@endif

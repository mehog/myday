@if (! $isPreview)
    @php
        $curtainDate = $event->wedding_date->format('d · m · Y');
        $curtainMeta = collect([$curtainDate, $event->location_name])->filter()->implode(' — ');

        $curtainPalettes = [
            'amber-gold' => [
                '--curtain-navy-1' => '#1a1208',
                '--curtain-navy-2' => '#2a1f0f',
                '--curtain-flap-2' => '#3d2910',
                '--curtain-liner' => '#f5e6c8',
                '--curtain-paper' => '#fbf6ee',
                '--curtain-ink' => '#1a1208',
                '--curtain-gold' => '#c9a227',
                '--curtain-bg-1' => '#0d0a04',
                '--curtain-bg-2' => '#1a1208',
            ],
            'royal-wedding' => [
                '--curtain-navy-1' => '#0f1a2e',
                '--curtain-navy-2' => '#1a2744',
                '--curtain-flap-2' => '#1e3a5f',
                '--curtain-liner' => '#d4e0f0',
                '--curtain-paper' => '#f8f6f0',
                '--curtain-ink' => '#0f1a2e',
                '--curtain-gold' => '#d4af37',
                '--curtain-bg-1' => '#080f1a',
                '--curtain-bg-2' => '#0f1a2e',
            ],
            'lavender-dream' => [
                '--curtain-navy-1' => '#2d2438',
                '--curtain-navy-2' => '#3d3249',
                '--curtain-flap-2' => '#9b7bb8',
                '--curtain-liner' => '#e8dff5',
                '--curtain-paper' => '#faf8fc',
                '--curtain-ink' => '#2d2438',
                '--curtain-gold' => '#c9b8e0',
                '--curtain-bg-1' => '#1a1220',
                '--curtain-bg-2' => '#2d2438',
            ],
            'winter-magic' => [
                '--curtain-navy-1' => '#1a2332',
                '--curtain-navy-2' => '#243044',
                '--curtain-flap-2' => '#7eb8da',
                '--curtain-liner' => '#e8f4fc',
                '--curtain-paper' => '#f0f8ff',
                '--curtain-ink' => '#1a2332',
                '--curtain-gold' => '#7eb8da',
                '--curtain-bg-1' => '#0d1520',
                '--curtain-bg-2' => '#1a2332',
            ],
            'pearl-white' => [
                '--curtain-navy-1' => '#8C7355',
                '--curtain-navy-2' => '#6E5A42',
                '--curtain-flap-2' => '#a88b6a',
                '--curtain-liner' => '#E8E0D5',
                '--curtain-paper' => '#FAFAF8',
                '--curtain-ink' => '#1C1917',
                '--curtain-gold' => '#8C7355',
                '--curtain-bg-1' => '#5a4535',
                '--curtain-bg-2' => '#6E5A42',
            ],
            'dusty-rose' => [
                '--curtain-navy-1' => '#B5706A',
                '--curtain-navy-2' => '#9A5A55',
                '--curtain-flap-2' => '#c98580',
                '--curtain-liner' => '#E8C9C4',
                '--curtain-paper' => '#F9F1EE',
                '--curtain-ink' => '#2D1B16',
                '--curtain-gold' => '#B5706A',
                '--curtain-bg-1' => '#7a3a35',
                '--curtain-bg-2' => '#9A5A55',
            ],
            'paper-ink' => [
                '--curtain-navy-1' => '#9A7B4F',
                '--curtain-navy-2' => '#7A623E',
                '--curtain-flap-2' => '#b8956a',
                '--curtain-liner' => '#C4B59A',
                '--curtain-paper' => '#F3EDE3',
                '--curtain-ink' => '#3A2E24',
                '--curtain-gold' => '#9A7B4F',
                '--curtain-bg-1' => '#5a4a32',
                '--curtain-bg-2' => '#7A623E',
            ],
        ];

        $curtainPalette = $curtainPalettes[$event->theme->value] ?? $curtainPalettes['amber-gold'];
    @endphp

    <style>
        :root {
            --curtain-open: 1300;
            --curtain-hold: 1000;
            --curtain-zoom: 600;
            @foreach ($curtainPalette as $var => $val)
                {{ $var }}: {{ $val }};
            @endforeach
        }

        .curtain-stage {
            position: fixed;
            inset: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 1600px;
            transition: opacity 0.5s ease;
            z-index: 100;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            background: radial-gradient(
                120% 90% at 50% 12%,
                color-mix(in srgb, var(--curtain-navy-2) 55%, white) 0%,
                color-mix(in srgb, var(--curtain-bg-2) 72%, white) 45%,
                color-mix(in srgb, var(--curtain-bg-1) 60%, white) 100%
            );
        }

        .curtain-stage.curtain-gone {
            opacity: 0;
            pointer-events: none;
        }

        .curtain-inner {
            position: relative;
            width: 300px;
            height: 380px;
            border-radius: 16px;
            overflow: hidden;
            z-index: 2;
            background: linear-gradient(180deg, #fffdf9, var(--curtain-paper));
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.28);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            transform: scale(0.96);
            opacity: 0.85;
            transition: transform 0.8s cubic-bezier(0.2, 0.9, 0.25, 1) 0.35s,
                opacity 0.6s ease 0.35s,
                filter 0.5s ease;
        }

        .curtain-inner::before {
            content: "";
            position: absolute;
            inset: 12px;
            border: 1px solid color-mix(in srgb, var(--curtain-gold) 55%, transparent);
            border-radius: 9px;
            pointer-events: none;
        }

        .curtain-inner-body {
            color: var(--curtain-ink);
            line-height: 1;
            padding: 0 22px;
        }

        .curtain-eyebrow {
            display: block;
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--curtain-gold);
            margin-bottom: 12px;
        }

        .curtain-names {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 600;
            font-style: italic;
            color: var(--curtain-ink);
        }

        .curtain-rule {
            width: 44px;
            height: 1px;
            background: var(--curtain-gold);
            margin: 14px auto 10px;
            position: relative;
        }

        .curtain-rule::after {
            content: "♥";
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -52%);
            background: var(--curtain-paper);
            color: var(--curtain-gold);
            font-size: 11px;
            padding: 0 6px;
        }

        .curtain-meta {
            font-size: 11px;
            letter-spacing: 2px;
            color: #9a8580;
            text-transform: uppercase;
        }

        .curtain-rod {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 15px;
            z-index: 5;
            background: linear-gradient(180deg,
                color-mix(in srgb, var(--curtain-gold), #fff 45%),
                var(--curtain-gold),
                color-mix(in srgb, var(--curtain-gold), #000 35%));
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .curtain-rod::before,
        .curtain-rod::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            transform: translateY(-50%);
            background: radial-gradient(circle at 35% 30%,
                color-mix(in srgb, var(--curtain-gold), #fff 55%),
                var(--curtain-gold) 60%,
                color-mix(in srgb, var(--curtain-gold), #000 40%));
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.35);
        }

        .curtain-rod::before { left: -6px; }
        .curtain-rod::after { right: -6px; }

        .curtain-panel {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 51%;
            z-index: 4;
            overflow: hidden;
            background:
                repeating-linear-gradient(90deg,
                    rgba(0, 0, 0, 0.30) 0, rgba(0, 0, 0, 0) 20px,
                    rgba(255, 255, 255, 0.09) 34px, rgba(0, 0, 0, 0) 48px, rgba(0, 0, 0, 0.30) 64px),
                linear-gradient(180deg,
                    color-mix(in srgb, var(--curtain-navy-2), #fff 10%),
                    var(--curtain-navy-1) 55%,
                    color-mix(in srgb, var(--curtain-navy-1), #000 18%));
            transition: transform 1.1s cubic-bezier(0.6, 0.02, 0.2, 1);
        }

        .curtain-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg, transparent 32%, rgba(255, 255, 255, 0.16) 48%, transparent 64%);
            transform: translateX(-60%);
            animation: curtain-shimmer 6.5s ease-in-out infinite;
        }

        @keyframes curtain-shimmer {
            0%, 100% { transform: translateX(-60%); }
            50% { transform: translateX(60%); }
        }

        .curtain-left {
            left: 0;
            transform-origin: left center;
            box-shadow: inset -20px 0 34px rgba(0, 0, 0, 0.4);
        }

        .curtain-right {
            right: 0;
            transform-origin: right center;
            box-shadow: inset 20px 0 34px rgba(0, 0, 0, 0.4);
        }

        /* draw the curtains apart — flip the rotateY sign if the fold looks off */
        .curtain-stage.curtain-parting .curtain-left {
            transform: translateX(-102%) rotateY(12deg) scaleX(0.9);
        }

        .curtain-stage.curtain-parting .curtain-right {
            transform: translateX(102%) rotateY(-12deg) scaleX(0.9);
        }

        .curtain-stage.curtain-parting .curtain-inner {
            transform: scale(1);
            opacity: 1;
        }

        .curtain-stage.curtain-zoom .curtain-inner {
            transform: scale(4.8) translateY(-6px);
            opacity: 0;
            filter: blur(6px);
            transition: transform 0.6s cubic-bezier(0.5, 0, 0.85, 0.35),
                opacity 0.5s ease-in 0.1s,
                filter 0.55s ease-in;
        }

        .curtain-hearts {
            position: absolute;
            inset: 0;
            z-index: 8;
            pointer-events: none;
            overflow: visible;
        }

        .curtain-hearts .curtain-h {
            position: absolute;
            will-change: transform, opacity;
            animation: curtain-rise var(--dur, 2.4s) ease-out forwards;
        }

        @keyframes curtain-rise {
            0% {
                transform: translate(-50%, 0) scale(0.2) rotate(0);
                opacity: 0;
            }
            14% { opacity: 1; }
            100% {
                transform: translate(calc(-50% + var(--dx)), var(--dy)) scale(var(--sc)) rotate(var(--rot));
                opacity: 0;
            }
        }

        .curtain-hint {
            position: absolute;
            left: 50%;
            bottom: 48px;
            transform: translateX(-50%);
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.7);
            z-index: 6;
            transition: opacity 0.3s ease;
            white-space: nowrap;
        }

        .curtain-stage.curtain-parting .curtain-hint {
            opacity: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            .curtain-panel::after { animation: none; }
            .curtain-panel,
            .curtain-inner { transition-duration: 0.001s; }
        }
    </style>

    <div
        class="curtain-stage"
        id="curtain-stage"
        wire:ignore
        role="button"
        tabindex="0"
        aria-label="{{ __('invitation.envelope_open') }}"
    >
        <div class="curtain-inner">
            <div class="curtain-inner-body">
                <span class="curtain-eyebrow">{{ __('invitation.save_the_date') }}</span>
                <div class="curtain-names">{{ $event->couple_names }}</div>
                <div class="curtain-rule"></div>
                @if ($curtainMeta)
                    <div class="curtain-meta">{{ $curtainMeta }}</div>
                @endif
            </div>
        </div>

        <div class="curtain-rod"></div>
        <div class="curtain-panel curtain-left"></div>
        <div class="curtain-panel curtain-right"></div>

        <div class="curtain-hearts" id="curtain-hearts" aria-hidden="true"></div>
        <span class="curtain-hint text-center">{{ __('invitation.envelope_tap') }}</span>
    </div>

    <script>
        (function () {
            const stage = document.getElementById('curtain-stage');
            const layer = document.getElementById('curtain-hearts');
            const colors = ['#e2506a', '#ff86a0', '#ffc9d4', '#c9a24b', '#ffffff'];
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const css = getComputedStyle(document.documentElement);
            const OPEN = parseInt(css.getPropertyValue('--curtain-open'), 10) || 1300;
            const HOLD = parseInt(css.getPropertyValue('--curtain-hold'), 10) || 1000;
            const ZOOM = parseInt(css.getPropertyValue('--curtain-zoom'), 10) || 600;

            const MAX_HEARTS = 24;
            let started = false;
            let heartTimer = null;
            let revealedToLivewire = false;

            document.body.style.overflow = 'hidden';

            function showInviteContent() {
                const el = document.getElementById('invitation-content');

                if (!el) {
                    return;
                }

                el.style.opacity = '1';
                el.style.pointerEvents = 'auto';

                if (revealedToLivewire) {
                    return;
                }

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
                document.body.style.overflow = '';
                showInviteContent();
                document.dispatchEvent(new CustomEvent('invitation:revealed'));
            }

            function spawnHeart() {
                if (!layer || layer.childElementCount >= MAX_HEARTS) {
                    return;
                }

                const el = document.createElement('span');
                el.className = 'curtain-h';
                const size = 10 + Math.random() * 14;
                el.style.left = (38 + Math.random() * 24) + '%';
                el.style.top = (36 + Math.random() * 20) + '%';
                el.style.setProperty('--dx', (Math.random() * 140 - 70).toFixed(0) + 'px');
                el.style.setProperty('--dy', (-160 - Math.random() * 130).toFixed(0) + 'px');
                el.style.setProperty('--sc', (0.7 + Math.random() * 0.7).toFixed(2));
                el.style.setProperty('--rot', (Math.random() * 120 - 60).toFixed(0) + 'deg');
                el.style.setProperty('--dur', (2 + Math.random() * 1.4).toFixed(2) + 's');
                const c = colors[Math.floor(Math.random() * colors.length)];
                el.innerHTML = `<svg width="${size}" height="${size}" viewBox="0 0 24 24">
                    <path d="M12 21c-5.6-3.8-9.6-6.9-9.6-11.3A4.7 4.7 0 0 1 12 6.3 4.7 4.7 0 0 1 21.6 9.7C21.6 14.1 17.6 17.2 12 21z" fill="${c}"/></svg>`;
                layer.appendChild(el);
                el.addEventListener('animationend', () => el.remove());
            }

            function reveal() {
                if (started) {
                    return;
                }

                started = true;
                window.envYtPlayOnGesture?.();

                if (reduce) {
                    stage.classList.add('curtain-gone');
                    finishReveal();
                    return;
                }

                stage.classList.add('curtain-parting');
                heartTimer = setInterval(spawnHeart, 170);

                setTimeout(() => {
                    clearInterval(heartTimer);
                    stage.classList.add('curtain-zoom');
                }, OPEN + HOLD);

                setTimeout(() => {
                    showInviteContent();
                }, OPEN + HOLD + 150);

                setTimeout(() => {
                    stage.classList.add('curtain-gone');
                    finishReveal();
                }, OPEN + HOLD + ZOOM);
            }

            stage.addEventListener('click', reveal);
            stage.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    reveal();
                }
            });
        })();
    </script>
@endif
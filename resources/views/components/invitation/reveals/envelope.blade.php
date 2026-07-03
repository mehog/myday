@if (! $isPreview)
    @php
        $envelopeDate = $event->wedding_date->format('d · m · Y');
        $envelopeMeta = collect([$envelopeDate, $event->location_name])->filter()->implode(' — ');

        $envPalettes = [
            'amber-gold' => [
                '--env-navy-1' => '#1a1208',
                '--env-navy-2' => '#2a1f0f',
                '--env-flap-2' => '#3d2910',
                '--env-liner' => '#f5e6c8',
                '--env-paper' => '#fbf6ee',
                '--env-ink' => '#1a1208',
                '--env-gold' => '#c9a227',
                '--env-bg-1' => '#0d0a04',
                '--env-bg-2' => '#1a1208',
            ],
            'royal-wedding' => [
                '--env-navy-1' => '#0f1a2e',
                '--env-navy-2' => '#1a2744',
                '--env-flap-2' => '#1e3a5f',
                '--env-liner' => '#d4e0f0',
                '--env-paper' => '#f8f6f0',
                '--env-ink' => '#0f1a2e',
                '--env-gold' => '#d4af37',
                '--env-bg-1' => '#080f1a',
                '--env-bg-2' => '#0f1a2e',
            ],
            'lavender-dream' => [
                '--env-navy-1' => '#2d2438',
                '--env-navy-2' => '#3d3249',
                '--env-flap-2' => '#9b7bb8',
                '--env-liner' => '#e8dff5',
                '--env-paper' => '#faf8fc',
                '--env-ink' => '#2d2438',
                '--env-gold' => '#c9b8e0',
                '--env-bg-1' => '#1a1220',
                '--env-bg-2' => '#2d2438',
            ],
            'winter-magic' => [
                '--env-navy-1' => '#1a2332',
                '--env-navy-2' => '#243044',
                '--env-flap-2' => '#7eb8da',
                '--env-liner' => '#e8f4fc',
                '--env-paper' => '#f0f8ff',
                '--env-ink' => '#1a2332',
                '--env-gold' => '#7eb8da',
                '--env-bg-1' => '#0d1520',
                '--env-bg-2' => '#1a2332',
            ],
            'pearl-white' => [
                '--env-navy-1' => '#8C7355',
                '--env-navy-2' => '#6E5A42',
                '--env-flap-2' => '#a88b6a',
                '--env-liner' => '#E8E0D5',
                '--env-paper' => '#FAFAF8',
                '--env-ink' => '#1C1917',
                '--env-gold' => '#8C7355',
                '--env-bg-1' => '#5a4535',
                '--env-bg-2' => '#6E5A42',
            ],
            'dusty-rose' => [
                '--env-navy-1' => '#B5706A',
                '--env-navy-2' => '#9A5A55',
                '--env-flap-2' => '#c98580',
                '--env-liner' => '#E8C9C4',
                '--env-paper' => '#F9F1EE',
                '--env-ink' => '#2D1B16',
                '--env-gold' => '#B5706A',
                '--env-bg-1' => '#7a3a35',
                '--env-bg-2' => '#9A5A55',
            ],
        ];

        $envPalette = $envPalettes[$event->theme->value] ?? $envPalettes['amber-gold'];
    @endphp

    <style>
        :root {
            --env-open: 1250;
            --env-hold: 1000;
            --env-zoom: 600;
            @foreach ($envPalette as $var => $val)
                {{ $var }}: {{ $val }};
            @endforeach
        }

        .env-stage {
            position: fixed;
            inset: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 1400px;
            transition: opacity 0.5s ease;
            z-index: 100;
            font-family: 'Montserrat', sans-serif;
            background: radial-gradient(
                120% 90% at 50% 8%,
                color-mix(in srgb, var(--env-navy-2) 50%, white) 0%,
                color-mix(in srgb, var(--env-bg-2) 72%, white) 42%,
                color-mix(in srgb, var(--env-bg-1) 58%, white) 100%
            );
        }

        .env-stage.env-gone {
            opacity: 0;
            pointer-events: none;
        }

        .env-envelope {
            position: relative;
            height: 210px;
            width: 320px;
            cursor: pointer;
            animation: env-bob 6s ease-in-out infinite;
        }

        .env-envelope.env-opening {
            animation: none;
            pointer-events: none;
        }

        @keyframes env-bob {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-9px); }
        }

        .env-envelope::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -34px;
            width: 74%;
            height: 26px;
            background: rgba(0, 0, 0, 0.4);
            filter: blur(16px);
            border-radius: 50%;
            transform: translateX(-50%);
            transition: 0.6s ease;
        }

        .env-envelope.env-opening::after {
            width: 60%;
            opacity: 0.6;
        }

        .env-back {
            position: absolute;
            inset: 0;
            border-radius: 16px;
            background: linear-gradient(150deg, var(--env-navy-1), var(--env-navy-2));
            box-shadow: inset 0 -14px 30px rgba(0, 0, 0, 0.18);
            z-index: 1;
        }

        .env-letter {
            position: absolute;
            left: 50%;
            top: 14px;
            width: 86%;
            height: 78%;
            transform: translate(-50%, 0);
            background: linear-gradient(180deg, #fffdf8, var(--env-paper));
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.14);
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            transition: transform 0.75s cubic-bezier(0.2, 0.9, 0.25, 1),
                opacity 0.5s ease,
                filter 0.5s ease;
        }

        .env-letter::before {
            content: "";
            position: absolute;
            inset: 7px;
            border: 1px solid rgba(201, 162, 75, 0.45);
            border-radius: 7px;
            pointer-events: none;
        }

        .env-letter-inner {
            color: var(--env-ink);
            line-height: 1;
        }

        .env-eyebrow {
            display: block;
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--env-gold);
            margin-bottom: 9px;
        }

        .env-names {
            font-family: 'Playfair Display', serif;
            font-size: 27px;
            font-weight: 600;
            font-style: italic;
            color: var(--env-ink);
        }

        .env-rule {
            width: 38px;
            height: 1px;
            background: var(--env-gold);
            margin: 10px auto 8px;
            position: relative;
        }

        .env-rule::after {
            content: "♥";
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -52%);
            background: var(--env-paper);
            color: var(--env-gold);
            font-size: 10px;
            padding: 0 5px;
        }

        .env-meta {
            font-size: 11px;
            letter-spacing: 2px;
            color: #7c7f97;
            text-transform: uppercase;
        }

        .env-pocket {
            position: absolute;
            inset: 0;
            border-radius: 16px;
            background: linear-gradient(150deg, var(--env-navy-2), var(--env-navy-1));
            clip-path: polygon(0 26%, 50% 74%, 100% 26%, 100% 100%, 0 100%);
            box-shadow: inset 0 10px 22px rgba(0, 0, 0, 0.16);
            z-index: 4;
        }

        .env-flap,
        .env-flap-back {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            border-right: 160px solid transparent;
            border-left: 160px solid transparent;
            border-bottom: 120px solid transparent;
            border-radius: 14px;
            transform-origin: top;
            transition: transform 0.55s cubic-bezier(0.6, 0.04, 0.28, 1);
            backface-visibility: hidden;
        }

        .env-flap {
            border-top: 105px solid var(--env-flap-2);
            z-index: 5;
            transform: rotateX(0deg);
            filter: drop-shadow(0 5px 4px rgba(0, 0, 0, 0.12));
        }

        .env-flap-back {
            border-top: 105px solid var(--env-liner);
            z-index: 3;
            transform: rotateX(90deg);
        }

        .env-seal {
            position: absolute;
            top: 66px;
            left: 50%;
            width: 40px;
            height: 40px;
            transform: translate(-50%, -50%);
            z-index: 6;
            transition: opacity 0.3s ease, transform 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .env-seal svg {
            width: 100%;
            height: 100%;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.35));
        }

        .env-envelope.env-opening .env-flap {
            transform: rotateX(90deg);
        }

        .env-envelope.env-opening .env-flap-back {
            transform: rotateX(180deg);
            transition-delay: 0.22s;
        }

        .env-envelope.env-opening .env-seal {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.5);
        }

        .env-envelope.env-opening .env-letter {
            transform: translate(-50%, -96px) scale(1.04);
            transition-delay: 0.5s;
            box-shadow: 0 22px 40px rgba(0, 0, 0, 0.3);
        }

        .env-envelope.env-zoom .env-letter {
            transform: translate(-50%, -150px) scale(5.2);
            opacity: 0;
            filter: blur(6px);
            transition: transform 0.6s cubic-bezier(0.5, 0, 0.85, 0.35),
                opacity 0.5s ease-in 0.1s,
                filter 0.55s ease-in;
        }

        .env-hearts {
            position: absolute;
            inset: 0;
            z-index: 7;
            pointer-events: none;
            overflow: visible;
        }

        .env-hearts .env-h {
            position: absolute;
            will-change: transform, opacity;
            animation: env-rise var(--dur, 2.4s) ease-out forwards;
        }

        @keyframes env-rise {
            0% {
                transform: translate(-50%, 0) scale(0.2) rotate(0);
                opacity: 0;
            }
            14% {
                opacity: 1;
            }
            100% {
                transform: translate(calc(-50% + var(--dx)), var(--dy)) scale(var(--sc)) rotate(var(--rot));
                opacity: 0;
            }
        }

        .env-hint {
            position: absolute;
            left: 50%;
            bottom: -64px;
            transform: translateX(-50%);
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            transition: opacity 0.3s ease;
        }

        .env-envelope.env-opening .env-hint {
            opacity: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            .env-envelope {
                animation: none;
            }

            .env-flap,
            .env-flap-back,
            .env-letter {
                transition-duration: 0.001s;
            }
        }
    </style>

    <div class="env-stage" id="env-stage" wire:ignore>
        <div
            class="env-envelope"
            id="env-envelope"
            role="button"
            tabindex="0"
            aria-label="{{ __('invitation.envelope_open') }}"
        >
            <div class="env-back"></div>
            <div class="env-letter">
                <div class="env-letter-inner">
                    <span class="env-eyebrow">{{ __('invitation.save_the_date') }}</span>
                    <div class="env-names">{{ $event->couple_names }}</div>
                    <div class="env-rule"></div>
                    @if ($envelopeMeta)
                        <div class="env-meta">{{ $envelopeMeta }}</div>
                    @endif
                </div>
            </div>
            <div class="env-pocket"></div>
            <div class="env-flap-back"></div>
            <div class="env-flap"></div>
            <div class="env-seal" aria-hidden="true">
                <svg viewBox="0 0 40 40">
                    <defs>
                        <radialGradient id="env-wax" cx="38%" cy="32%" r="75%">
                            <stop offset="0%" stop-color="#f0a9bb"/>
                            <stop offset="55%" stop-color="#d75f7c"/>
                            <stop offset="100%" stop-color="#a83b57"/>
                        </radialGradient>
                    </defs>
                    <circle cx="20" cy="20" r="18" fill="url(#env-wax)"/>
                    <path d="M20 27c-4.5-3-8-5.4-8-9a3.6 3.6 0 0 1 7-1.2A3.6 3.6 0 0 1 28 18c0 3.6-3.5 6-8 9z" fill="#f6dbe0" opacity=".92"/>
                </svg>
            </div>
            <div class="env-hearts" id="env-hearts" aria-hidden="true"></div>
            <span class="env-hint text-center">{{ __('invitation.envelope_tap') }}</span>
        </div>
    </div>

    <script>
        (function () {
            const env = document.getElementById('env-envelope');
            const stage = document.getElementById('env-stage');
            const layer = document.getElementById('env-hearts');
            const colors = ['#e2506a', '#ff86a0', '#ffc9d4', '#c9a24b', '#ffffff'];
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const css = getComputedStyle(document.documentElement);
            const OPEN = parseInt(css.getPropertyValue('--env-open'), 10) || 1250;
            const HOLD = parseInt(css.getPropertyValue('--env-hold'), 10) || 1000;
            const ZOOM = parseInt(css.getPropertyValue('--env-zoom'), 10) || 600;

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
                el.className = 'env-h';
                const size = 10 + Math.random() * 14;
                el.style.left = (26 + Math.random() * 48) + '%';
                el.style.top = (30 + Math.random() * 18) + '%';
                el.style.setProperty('--dx', (Math.random() * 120 - 60).toFixed(0) + 'px');
                el.style.setProperty('--dy', (-150 - Math.random() * 120).toFixed(0) + 'px');
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
                    stage.classList.add('env-gone');
                    finishReveal();
                    return;
                }

                env.classList.add('env-opening');
                heartTimer = setInterval(spawnHeart, 170);

                setTimeout(() => {
                    clearInterval(heartTimer);
                    env.classList.add('env-zoom');
                }, OPEN + HOLD);

                setTimeout(() => {
                    showInviteContent();
                }, OPEN + HOLD + 150);

                setTimeout(() => {
                    stage.classList.add('env-gone');
                    finishReveal();
                }, OPEN + HOLD + ZOOM);
            }

            env.addEventListener('click', reveal);
            env.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    reveal();
                }
            });
        })();
    </script>
@endif

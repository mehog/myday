@if (! $isPreview)
    @php
        $gateDate = $event->wedding_date->format('d · m · Y');
        $gateMeta = collect([$gateDate, $event->location_name])->filter()->implode(' — ');

        $gateAccents = [
            'amber-gold' => ['ink' => '#332516', 'gold' => '#b88b42', 'moon' => '#c8d4e8'],
            'royal-wedding' => ['ink' => '#172944', 'gold' => '#b18b32', 'moon' => '#b8cce8'],
            'lavender-dream' => ['ink' => '#463653', 'gold' => '#9b7bb8', 'moon' => '#c8d4f0'],
            'winter-magic' => ['ink' => '#20364b', 'gold' => '#6299ba', 'moon' => '#d0e4f4'],
            'pearl-white' => ['ink' => '#33291f', 'gold' => '#8C7355', 'moon' => '#d8e0ea'],
            'dusty-rose' => ['ink' => '#532f2c', 'gold' => '#B5706A', 'moon' => '#d4c8e0'],
            'paper-ink' => ['ink' => '#3A2E24', 'gold' => '#9A7B4F', 'moon' => '#ccd8e8'],
        ];

        $gateAccent = $gateAccents[$event->theme->value] ?? $gateAccents['amber-gold'];
        $gateClosedUrl = asset('img/garden-gate-reveal/nasdan-garden-gate-closed.webp');
        $gateOpenUrl = asset('img/garden-gate-reveal/nasdan-garden-gate-open.webp');
    @endphp

    <style>
        :root {
            --gate-crossfade: 1350;
            --gate-hold: 900;
            --gate-zoom: 900;
            --gate-ink: {{ $gateAccent['ink'] }};
            --gate-gold: {{ $gateAccent['gold'] }};
            --gate-moon: {{ $gateAccent['moon'] }};
        }

        .gate-photo-stage,
        .gate-photo-stage * {
            box-sizing: border-box;
        }

        .gate-photo-stage {
            --gate-closed-image: url('{{ $gateClosedUrl }}');
            position: fixed;
            inset: 0;
            z-index: 100;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: hidden;
            isolation: isolate;
            background: #0a1218;
            opacity: 1;
            transition: opacity 0.78s ease, filter 0.78s ease;
        }

        .gate-photo-stage::before {
            content: '';
            position: absolute;
            z-index: -2;
            inset: -28px;
            background-image: var(--gate-closed-image);
            background-position: center;
            background-size: cover;
            filter: blur(22px) saturate(0.65) brightness(0.82);
            transform: scale(1.08);
        }

        .gate-photo-stage::after {
            content: '';
            position: absolute;
            z-index: 8;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 28%, rgba(196, 220, 248, 0.42), transparent 38%);
            opacity: 0;
            transition: opacity 0.55s ease;
        }

        .gate-photo-stage.gate-is-opening::after {
            animation: gate-stage-bloom 1.25s ease-out both;
        }

        .gate-photo-stage.gate-is-fading {
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            pointer-events: none;
        }

        @keyframes gate-stage-bloom {
            0%, 100% { opacity: 0; }
            35% { opacity: 0.58; }
        }

        .gate-photo-trigger {
            position: absolute;
            inset: 0;
            display: block;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            border: 0;
            border-radius: 0;
            font: inherit;
            color: inherit;
            background: transparent;
            cursor: pointer;
            outline: none;
            touch-action: manipulation;
            -webkit-appearance: none;
            -webkit-tap-highlight-color: transparent;
        }

        .gate-photo-trigger:focus-visible {
            outline: 2px solid rgba(184, 139, 66, 0.95);
            outline-offset: -8px;
        }

        .gate-photo-trigger[aria-expanded='true'] {
            cursor: default;
            pointer-events: none;
        }

        .gate-photo-media {
            position: absolute;
            inset: 0;
            overflow: hidden;
            background: #060c10;
            transform: scale(1);
            transform-origin: 50% 46%;
            transition: transform 0.95s cubic-bezier(0.65, 0, 0.35, 1), filter 0.7s ease;
            will-change: transform, filter;
        }

        .gate-photo-media::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 4;
            pointer-events: none;
            background:
                linear-gradient(180deg, rgba(180, 210, 240, 0.12), transparent 28%),
                radial-gradient(ellipse at center, transparent 52%, rgba(6, 12, 16, 0.28) 100%);
        }

        .gate-photo-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center center;
            user-select: none;
            -webkit-user-drag: none;
            will-change: opacity, transform, filter;
        }

        .gate-photo-open {
            z-index: 1;
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            transform: scale(1.03);
            transition:
                opacity 1.15s cubic-bezier(0.4, 0, 0.2, 1) 0.18s,
                transform 1.55s cubic-bezier(0.16, 1, 0.3, 1) 0.12s,
                filter 1.05s ease 0.12s;
        }

        .gate-panels {
            position: absolute;
            inset: 0;
            z-index: 3;
            perspective: 1400px;
        }

        .gate-panel {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 50.5%;
            overflow: hidden;
            transform: translateX(0) rotateY(0deg);
            transition:
                transform 1.15s cubic-bezier(0.55, 0.02, 0.18, 1),
                opacity 0.85s ease 0.35s,
                filter 0.9s ease;
            will-change: transform, opacity, filter;
        }

        .gate-panel-left {
            left: 0;
            transform-origin: left center;
            box-shadow: inset -18px 0 36px rgba(0, 0, 0, 0.42);
        }

        .gate-panel-right {
            right: 0;
            transform-origin: right center;
            box-shadow: inset 18px 0 36px rgba(0, 0, 0, 0.42);
        }

        .gate-panel-image {
            position: absolute;
            top: 0;
            width: 200%;
            height: 100%;
            max-width: none;
            object-fit: cover;
            object-position: center center;
            user-select: none;
            -webkit-user-drag: none;
        }

        .gate-panel-left .gate-panel-image {
            left: 0;
            object-position: left center;
        }

        .gate-panel-right .gate-panel-image {
            right: 0;
            object-position: right center;
        }

        .gate-is-pressing .gate-panel-left,
        .gate-is-pressing .gate-panel-right {
            transform: scale(0.992);
            filter: brightness(0.96);
        }

        .gate-is-opening .gate-photo-open {
            opacity: 1;
            filter: brightness(1) blur(0);
            transform: scale(1);
        }

        .gate-is-opening .gate-panel-left {
            transform: translateX(-104%) rotateY(14deg) scaleX(0.94);
            opacity: 0.92;
            filter: brightness(0.92);
        }

        .gate-is-opening .gate-panel-right {
            transform: translateX(104%) rotateY(-14deg) scaleX(0.94);
            opacity: 0.92;
            filter: brightness(0.92);
        }

        .gate-is-zooming .gate-photo-media {
            filter: brightness(1.12);
            transform: scale(1.16);
        }

        .gate-latch-light {
            position: absolute;
            z-index: 6;
            left: 50%;
            top: 48%;
            width: clamp(44px, 12vw, 88px);
            height: clamp(44px, 12vw, 88px);
            border-radius: 50%;
            opacity: 0;
            pointer-events: none;
            background: radial-gradient(
                circle at center,
                rgba(255, 232, 168, 0.82) 0%,
                rgba(212, 176, 96, 0.32) 42%,
                transparent 72%
            );
            transform: translate(-50%, -50%) scale(0.5);
        }

        .gate-is-pressing .gate-latch-light {
            animation: gate-latch-glint 0.42s ease-out both;
        }

        @keyframes gate-latch-glint {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
            40% { opacity: 0.95; transform: translate(-50%, -50%) scale(1); }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.35); }
        }

        .gate-moonlight {
            position: absolute;
            z-index: 6;
            left: 50%;
            top: 28%;
            width: clamp(140px, 38vw, 280px);
            height: clamp(120px, 28vh, 240px);
            border-radius: 50%;
            opacity: 0;
            pointer-events: none;
            background: radial-gradient(
                ellipse at center,
                color-mix(in srgb, var(--gate-moon) 78%, white) 0%,
                rgba(180, 210, 240, 0.24) 38%,
                transparent 72%
            );
            transform: translate(-50%, -50%) scale(0.4);
        }

        .gate-is-opening .gate-moonlight {
            animation: gate-moon-bloom 1.15s cubic-bezier(0.16, 1, 0.3, 1) 0.12s both;
        }

        @keyframes gate-moon-bloom {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.4); }
            28% { opacity: 0.92; }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.4); }
        }

        .gate-open-copy {
            position: absolute;
            z-index: 5;
            left: 50%;
            top: 31%;
            width: min(70vw, 520px);
            color: var(--gate-ink);
            text-align: center;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.65);
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, 28px) scale(0.97);
            transition:
                opacity 0.9s ease 0.72s,
                transform 1.05s cubic-bezier(0.16, 1, 0.3, 1) 0.7s,
                filter 0.8s ease 0.7s;
            pointer-events: none;
        }

        .gate-is-opening .gate-open-copy {
            opacity: 1;
            filter: blur(0);
            transform: translate(-50%, 0) scale(1);
        }

        .gate-is-zooming .gate-open-copy {
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, -12px) scale(1.12);
            transition:
                opacity 0.52s ease,
                transform 0.85s cubic-bezier(0.65, 0, 0.35, 1),
                filter 0.55s ease;
        }

        .gate-open-ornament {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(10px, 2.2vw, 18px);
            margin-bottom: clamp(12px, 2.2vh, 22px);
            color: var(--gate-gold);
        }

        .gate-open-ornament::before,
        .gate-open-ornament::after {
            content: '';
            width: clamp(34px, 10vw, 84px);
            height: 1px;
            background: linear-gradient(90deg, transparent, currentColor);
        }

        .gate-open-ornament::after {
            background: linear-gradient(90deg, currentColor, transparent);
        }

        .gate-open-ornament svg {
            width: clamp(15px, 3.2vw, 23px);
            height: auto;
        }

        .gate-open-eyebrow,
        .gate-open-names,
        .gate-open-meta {
            display: block;
        }

        .gate-open-eyebrow {
            margin-bottom: clamp(9px, 1.8vh, 16px);
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.2vw, 13px);
            font-weight: 500;
            line-height: 1.35;
            letter-spacing: clamp(3px, 0.8vw, 6px);
            text-transform: uppercase;
            color: var(--gate-gold);
        }

        .gate-open-names {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(34px, 9vw, 76px);
            font-weight: 600;
            font-style: italic;
            line-height: 1.03;
            text-wrap: balance;
        }

        .gate-open-rule {
            display: block;
            width: clamp(62px, 16vw, 120px);
            height: 1px;
            margin: clamp(15px, 2.5vh, 24px) auto clamp(11px, 2vh, 18px);
            background: linear-gradient(90deg, transparent, var(--gate-gold), transparent);
        }

        .gate-open-meta {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(8px, 2vw, 12px);
            font-weight: 500;
            line-height: 1.55;
            letter-spacing: clamp(1.3px, 0.45vw, 3px);
            text-transform: uppercase;
            color: color-mix(in srgb, var(--gate-ink) 72%, white);
        }

        .gate-tap-hint {
            position: absolute;
            z-index: 7;
            left: 50%;
            bottom: max(7vh, calc(env(safe-area-inset-bottom) + 26px));
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: max-content;
            max-width: 88vw;
            padding: 12px 18px;
            border: 1px solid rgba(140, 170, 200, 0.22);
            border-radius: 999px;
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.4vw, 12px);
            font-weight: 500;
            line-height: 1;
            letter-spacing: clamp(3px, 0.9vw, 6px);
            text-transform: uppercase;
            white-space: nowrap;
            color: rgba(232, 240, 248, 0.84);
            background: rgba(10, 18, 24, 0.48);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(8px);
            transform: translateX(-50%);
            transition: opacity 0.4s ease, transform 0.5s ease;
        }

        .gate-tap-hint::before {
            content: '';
            width: 6px;
            height: 6px;
            flex: 0 0 auto;
            border-radius: 50%;
            background: var(--gate-gold);
            animation: gate-tap-pulse 1.9s ease-out infinite;
        }

        .gate-is-pressing .gate-tap-hint,
        .gate-is-opening .gate-tap-hint {
            opacity: 0;
            transform: translate(-50%, 12px);
        }

        @keyframes gate-tap-pulse {
            0% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--gate-gold) 45%, transparent); }
            100% { box-shadow: 0 0 0 10px transparent; }
        }

        .gate-photo-particles {
            position: absolute;
            z-index: 7;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .gate-photo-particle {
            position: absolute;
            left: 50%;
            top: 46%;
            width: var(--size);
            height: var(--size);
            border-radius: var(--shape, 50%);
            background: #a8d48a;
            box-shadow: 0 0 10px rgba(168, 212, 136, 0.65);
            opacity: 0;
            animation: gate-photo-firefly var(--duration) ease-out forwards;
        }

        @keyframes gate-photo-firefly {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.2); }
            18% { opacity: 0.92; }
            100% {
                opacity: 0;
                transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(var(--scale));
            }
        }

        @media (orientation: landscape) {
            .gate-photo-image,
            .gate-panel-image {
                object-fit: contain;
            }

            .gate-open-copy {
                top: 22%;
                width: min(38vw, 460px);
            }

            .gate-open-names {
                font-size: clamp(30px, 5vw, 58px);
            }

            .gate-tap-hint {
                bottom: max(5vh, calc(env(safe-area-inset-bottom) + 18px));
            }
        }

        @media (max-width: 430px) and (min-height: 760px) {
            .gate-open-copy {
                top: 28%;
                width: 72vw;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .gate-tap-hint::before,
            .gate-photo-particle,
            .gate-is-pressing .gate-latch-light,
            .gate-is-opening .gate-moonlight,
            .gate-photo-stage.gate-is-opening::after {
                animation: none;
            }

            .gate-photo-stage,
            .gate-photo-media,
            .gate-photo-image,
            .gate-panel,
            .gate-panel-image,
            .gate-open-copy,
            .gate-tap-hint {
                transition-duration: 0.01ms !important;
                transition-delay: 0ms !important;
            }

            .gate-is-opening .gate-panel-left,
            .gate-is-opening .gate-panel-right {
                transform: none;
                opacity: 0;
            }
        }
    </style>

    <div
        class="gate-photo-stage"
        id="gate-photo-stage"
        wire:ignore
    >
        <button
            type="button"
            class="gate-photo-trigger"
            id="gate-photo-trigger"
            aria-label="{{ __('invitation.envelope_open') }}"
            aria-expanded="false"
        >
            <span class="gate-photo-media" aria-hidden="true">
                <img
                    class="gate-photo-image gate-photo-open"
                    src="{{ $gateOpenUrl }}"
                    alt=""
                    loading="eager"
                    decoding="async"
                >

                <span class="gate-panels">
                    <span class="gate-panel gate-panel-left">
                        <img
                            class="gate-panel-image"
                            src="{{ $gateClosedUrl }}"
                            alt=""
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        >
                    </span>
                    <span class="gate-panel gate-panel-right">
                        <img
                            class="gate-panel-image"
                            src="{{ $gateClosedUrl }}"
                            alt=""
                            loading="eager"
                            decoding="async"
                        >
                    </span>
                </span>
            </span>

            <span class="gate-latch-light" aria-hidden="true"></span>
            <span class="gate-moonlight" aria-hidden="true"></span>
            <span class="gate-photo-particles" id="gate-photo-particles" aria-hidden="true"></span>

            <span class="gate-open-copy">
                <span class="gate-open-ornament" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 21c-5.4-3.65-8.8-6.5-8.8-10.45A4.55 4.55 0 0 1 12 8.92a4.55 4.55 0 0 1 8.8 1.63C20.8 14.5 17.4 17.35 12 21Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="gate-open-eyebrow">{{ __('invitation.save_the_date') }}</span>
                <span class="gate-open-names">{{ $event->couple_names }}</span>
                <span class="gate-open-rule" aria-hidden="true"></span>
                @if ($gateMeta)
                    <span class="gate-open-meta">{{ $gateMeta }}</span>
                @endif
            </span>

            <span class="gate-tap-hint">{{ __('invitation.envelope_tap') }}</span>
        </button>
    </div>

    <script>
        (function () {
            const stage = document.getElementById('gate-photo-stage');
            const trigger = document.getElementById('gate-photo-trigger');
            const particleLayer = document.getElementById('gate-photo-particles');

            if (!stage || !trigger) {
                return;
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const css = getComputedStyle(document.documentElement);
            const CROSSFADE = parseInt(css.getPropertyValue('--gate-crossfade'), 10) || 1350;
            const HOLD = parseInt(css.getPropertyValue('--gate-hold'), 10) || 900;
            const ZOOM = parseInt(css.getPropertyValue('--gate-zoom'), 10) || 900;
            const previousBodyOverflow = document.body.style.overflow;

            let started = false;
            let finished = false;
            let revealedToLivewire = false;

            document.body.style.overflow = 'hidden';

            function showInviteContent() {
                const content = document.getElementById('invitation-content');

                if (!content) {
                    return;
                }

                content.style.opacity = '1';
                content.style.pointerEvents = 'auto';

                if (revealedToLivewire) {
                    return;
                }

                const root = content.closest('[wire\\:id]');

                if (root && window.Livewire) {
                    const component = Livewire.find(root.getAttribute('wire:id'));

                    if (component) {
                        component.set('invitationRevealed', true);
                        revealedToLivewire = true;
                    }
                }
            }

            function finishReveal() {
                if (finished) {
                    return;
                }

                finished = true;
                document.body.style.overflow = previousBodyOverflow;
                showInviteContent();
                document.dispatchEvent(new CustomEvent('invitation:revealed'));

                window.setTimeout(() => {
                    stage.hidden = true;
                }, 800);
            }

            function spawnFirefly(index) {
                if (!particleLayer || particleLayer.childElementCount >= 22) {
                    return;
                }

                const particle = document.createElement('span');
                particle.className = 'gate-photo-particle';
                particle.style.setProperty('--size', (2 + Math.random() * 5).toFixed(1) + 'px');
                particle.style.setProperty('--dx', (Math.random() * 240 - 120).toFixed(0) + 'px');
                particle.style.setProperty('--dy', (Math.random() * 220 - 140).toFixed(0) + 'px');
                particle.style.setProperty('--scale', (0.5 + Math.random() * 1.1).toFixed(2));
                particle.style.setProperty('--duration', (0.85 + Math.random() * 0.85).toFixed(2) + 's');

                const palette = ['#a8d48a', '#d4e4f0', '#e4bc70', '#c8e8b8'];
                particle.style.background = palette[index % palette.length];
                particle.style.boxShadow = '0 0 10px color-mix(in srgb, ' + palette[index % palette.length] + ' 70%, transparent)';

                if (index % 5 === 0) {
                    particle.style.setProperty('--shape', '2px');
                }

                particleLayer.appendChild(particle);
                particle.addEventListener('animationend', () => particle.remove(), { once: true });
            }

            function releaseFireflies() {
                for (let index = 0; index < 20; index++) {
                    window.setTimeout(() => spawnFirefly(index), index * 32);
                }
            }

            function beginOpening() {
                stage.classList.remove('gate-is-pressing');
                stage.classList.add('gate-is-opening');

                if (reduceMotion) {
                    window.setTimeout(showInviteContent, 180);
                    window.setTimeout(() => {
                        stage.classList.add('gate-is-fading');
                        finishReveal();
                    }, 420);
                    return;
                }

                window.setTimeout(releaseFireflies, 160);

                window.setTimeout(() => {
                    stage.classList.add('gate-is-zooming');
                }, CROSSFADE + HOLD);

                window.setTimeout(showInviteContent, CROSSFADE + HOLD + 170);

                window.setTimeout(() => {
                    stage.classList.add('gate-is-fading');
                    finishReveal();
                }, CROSSFADE + HOLD + ZOOM);
            }

            function reveal() {
                if (started) {
                    return;
                }

                started = true;
                trigger.setAttribute('aria-expanded', 'true');

                window.envYtPlayOnGesture?.();

                stage.classList.add('gate-is-pressing');

                if (reduceMotion) {
                    beginOpening();
                    return;
                }

                window.setTimeout(beginOpening, 120);
            }

            trigger.addEventListener('click', reveal, { once: true });
        })();
    </script>
@endif

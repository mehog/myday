@if (! $isPreview)
    @php
        $curtainDate = $event->wedding_date->format('d · m · Y');
        $curtainMeta = collect([$curtainDate, $event->location_name])->filter()->implode(' — ');

        $curtainAccents = [
            'amber-gold' => ['ink' => '#332516', 'gold' => '#b88b42'],
            'royal-wedding' => ['ink' => '#172944', 'gold' => '#b18b32'],
            'lavender-dream' => ['ink' => '#463653', 'gold' => '#9b7bb8'],
            'winter-magic' => ['ink' => '#20364b', 'gold' => '#6299ba'],
            'pearl-white' => ['ink' => '#33291f', 'gold' => '#8C7355'],
            'dusty-rose' => ['ink' => '#532f2c', 'gold' => '#B5706A'],
            'paper-ink' => ['ink' => '#3A2E24', 'gold' => '#9A7B4F'],
        ];

        $curtainAccent = $curtainAccents[$event->theme->value] ?? $curtainAccents['amber-gold'];
        $curtainClosedUrl = asset('img/curtain-reveal/nasdan-curtain-closed.webp');
        $curtainOpenUrl = asset('img/curtain-reveal/nasdan-curtain-open.webp');
    @endphp

    <style>
        :root {
            --curtain-crossfade: 1350;
            --curtain-hold: 900;
            --curtain-zoom: 900;
            --curtain-ink: {{ $curtainAccent['ink'] }};
            --curtain-gold: {{ $curtainAccent['gold'] }};
        }

        .curtain-photo-stage,
        .curtain-photo-stage * {
            box-sizing: border-box;
        }

        .curtain-photo-stage {
            --curtain-closed-image: url('{{ $curtainClosedUrl }}');
            position: fixed;
            inset: 0;
            z-index: 100;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: hidden;
            isolation: isolate;
            background: #1a0f12;
            opacity: 1;
            transition: opacity 0.78s ease, filter 0.78s ease;
        }

        .curtain-photo-stage::before {
            content: '';
            position: absolute;
            z-index: -2;
            inset: -28px;
            background-image: var(--curtain-closed-image);
            background-position: center;
            background-size: cover;
            filter: blur(22px) saturate(0.72) brightness(0.88);
            transform: scale(1.08);
        }

        .curtain-photo-stage::after {
            content: '';
            position: absolute;
            z-index: 8;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 42%, rgba(255, 244, 214, 0.58), transparent 34%);
            opacity: 0;
            transition: opacity 0.55s ease;
        }

        .curtain-photo-stage.curtain-is-opening::after {
            animation: curtain-stage-bloom 1.25s ease-out both;
        }

        .curtain-photo-stage.curtain-is-fading {
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            pointer-events: none;
        }

        @keyframes curtain-stage-bloom {
            0%, 100% { opacity: 0; }
            35% { opacity: 0.62; }
        }

        .curtain-photo-trigger {
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

        .curtain-photo-trigger:focus-visible {
            outline: 2px solid rgba(184, 139, 66, 0.95);
            outline-offset: -8px;
        }

        .curtain-photo-trigger[aria-expanded='true'] {
            cursor: default;
            pointer-events: none;
        }

        .curtain-photo-media {
            position: absolute;
            inset: 0;
            overflow: hidden;
            background: #120a0d;
            transform: scale(1);
            transform-origin: 50% 42%;
            transition: transform 0.95s cubic-bezier(0.65, 0, 0.35, 1), filter 0.7s ease;
            will-change: transform, filter;
        }

        .curtain-photo-media::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 4;
            pointer-events: none;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.06), transparent 22%),
                radial-gradient(ellipse at center, transparent 52%, rgba(20, 8, 12, 0.22) 100%);
        }

        .curtain-photo-image {
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

        .curtain-photo-open {
            z-index: 1;
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            transform: scale(1.03);
            transition:
                opacity 1.15s cubic-bezier(0.4, 0, 0.2, 1) 0.18s,
                transform 1.55s cubic-bezier(0.16, 1, 0.3, 1) 0.12s,
                filter 1.05s ease 0.12s;
        }

        .curtain-panels {
            position: absolute;
            inset: 0;
            z-index: 3;
            perspective: 1400px;
        }

        .curtain-panel {
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

        .curtain-panel-left {
            left: 0;
            transform-origin: left center;
            box-shadow: inset -18px 0 36px rgba(0, 0, 0, 0.35);
        }

        .curtain-panel-right {
            right: 0;
            transform-origin: right center;
            box-shadow: inset 18px 0 36px rgba(0, 0, 0, 0.35);
        }

        .curtain-panel-image {
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

        .curtain-panel-left .curtain-panel-image {
            left: 0;
            object-position: left center;
        }

        .curtain-panel-right .curtain-panel-image {
            right: 0;
            object-position: right center;
        }

        .curtain-is-opening .curtain-photo-open {
            opacity: 1;
            filter: brightness(1) blur(0);
            transform: scale(1);
        }

        .curtain-is-opening .curtain-panel-left {
            transform: translateX(-104%) rotateY(14deg) scaleX(0.94);
            opacity: 0.92;
            filter: brightness(0.92);
        }

        .curtain-is-opening .curtain-panel-right {
            transform: translateX(104%) rotateY(-14deg) scaleX(0.94);
            opacity: 0.92;
            filter: brightness(0.92);
        }

        .curtain-is-zooming .curtain-photo-media {
            filter: brightness(1.1);
            transform: scale(1.16);
        }

        .curtain-center-light {
            position: absolute;
            z-index: 6;
            left: 50%;
            top: 42%;
            width: clamp(120px, 34vw, 260px);
            height: clamp(160px, 42vh, 340px);
            border-radius: 50%;
            opacity: 0;
            pointer-events: none;
            background: radial-gradient(
                ellipse at center,
                rgba(255, 248, 220, 0.72) 0%,
                rgba(255, 228, 160, 0.28) 38%,
                transparent 72%
            );
            transform: translate(-50%, -50%) scale(0.4);
        }

        .curtain-is-opening .curtain-center-light {
            animation: curtain-center-bloom 1.1s cubic-bezier(0.16, 1, 0.3, 1) 0.08s both;
        }

        @keyframes curtain-center-bloom {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.4); }
            28% { opacity: 0.95; }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.35); }
        }

        .curtain-open-copy {
            position: absolute;
            z-index: 5;
            left: 50%;
            top: 31%;
            width: min(70vw, 520px);
            color: var(--curtain-ink);
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

        .curtain-is-opening .curtain-open-copy {
            opacity: 1;
            filter: blur(0);
            transform: translate(-50%, 0) scale(1);
        }

        .curtain-is-zooming .curtain-open-copy {
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, -12px) scale(1.12);
            transition:
                opacity 0.52s ease,
                transform 0.85s cubic-bezier(0.65, 0, 0.35, 1),
                filter 0.55s ease;
        }

        .curtain-open-ornament {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(10px, 2.2vw, 18px);
            margin-bottom: clamp(12px, 2.2vh, 22px);
            color: var(--curtain-gold);
        }

        .curtain-open-ornament::before,
        .curtain-open-ornament::after {
            content: '';
            width: clamp(34px, 10vw, 84px);
            height: 1px;
            background: linear-gradient(90deg, transparent, currentColor);
        }

        .curtain-open-ornament::after {
            background: linear-gradient(90deg, currentColor, transparent);
        }

        .curtain-open-ornament svg {
            width: clamp(15px, 3.2vw, 23px);
            height: auto;
        }

        .curtain-open-eyebrow,
        .curtain-open-names,
        .curtain-open-meta {
            display: block;
        }

        .curtain-open-eyebrow {
            margin-bottom: clamp(9px, 1.8vh, 16px);
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.2vw, 13px);
            font-weight: 500;
            line-height: 1.35;
            letter-spacing: clamp(3px, 0.8vw, 6px);
            text-transform: uppercase;
            color: var(--curtain-gold);
        }

        .curtain-open-names {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(34px, 9vw, 76px);
            font-weight: 600;
            font-style: italic;
            line-height: 1.03;
            text-wrap: balance;
        }

        .curtain-open-rule {
            display: block;
            width: clamp(62px, 16vw, 120px);
            height: 1px;
            margin: clamp(15px, 2.5vh, 24px) auto clamp(11px, 2vh, 18px);
            background: linear-gradient(90deg, transparent, var(--curtain-gold), transparent);
        }

        .curtain-open-meta {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(8px, 2vw, 12px);
            font-weight: 500;
            line-height: 1.55;
            letter-spacing: clamp(1.3px, 0.45vw, 3px);
            text-transform: uppercase;
            color: color-mix(in srgb, var(--curtain-ink) 72%, white);
        }

        .curtain-tap-hint {
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
            border: 1px solid rgba(121, 89, 48, 0.18);
            border-radius: 999px;
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.4vw, 12px);
            font-weight: 500;
            line-height: 1;
            letter-spacing: clamp(3px, 0.9vw, 6px);
            text-transform: uppercase;
            white-space: nowrap;
            color: rgba(255, 248, 240, 0.82);
            background: rgba(26, 18, 8, 0.42);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.18);
            backdrop-filter: blur(8px);
            transform: translateX(-50%);
            transition: opacity 0.4s ease, transform 0.5s ease;
        }

        .curtain-tap-hint::before {
            content: '';
            width: 6px;
            height: 6px;
            flex: 0 0 auto;
            border-radius: 50%;
            background: var(--curtain-gold);
            animation: curtain-tap-pulse 1.9s ease-out infinite;
        }

        .curtain-is-opening .curtain-tap-hint {
            opacity: 0;
            transform: translate(-50%, 12px);
        }

        @keyframes curtain-tap-pulse {
            0% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--curtain-gold) 45%, transparent); }
            100% { box-shadow: 0 0 0 10px transparent; }
        }

        .curtain-photo-particles {
            position: absolute;
            z-index: 7;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .curtain-photo-particle {
            position: absolute;
            left: 50%;
            top: 42%;
            width: var(--size);
            height: var(--size);
            border-radius: 50%;
            background: #e4bc70;
            box-shadow: 0 0 10px rgba(228, 188, 112, 0.7);
            opacity: 0;
            animation: curtain-photo-dust var(--duration) ease-out forwards;
        }

        @keyframes curtain-photo-dust {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.2); }
            18% { opacity: 0.9; }
            100% {
                opacity: 0;
                transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(var(--scale));
            }
        }

        @media (orientation: landscape) {
            .curtain-photo-image,
            .curtain-panel-image {
                object-fit: contain;
            }

            .curtain-open-copy {
                top: 22%;
                width: min(38vw, 460px);
            }

            .curtain-open-names {
                font-size: clamp(30px, 5vw, 58px);
            }

            .curtain-tap-hint {
                bottom: max(5vh, calc(env(safe-area-inset-bottom) + 18px));
            }
        }

        @media (max-width: 430px) and (min-height: 760px) {
            .curtain-open-copy {
                top: 28%;
                width: 72vw;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .curtain-tap-hint::before,
            .curtain-photo-particle,
            .curtain-is-opening .curtain-center-light,
            .curtain-photo-stage.curtain-is-opening::after {
                animation: none;
            }

            .curtain-photo-stage,
            .curtain-photo-media,
            .curtain-photo-image,
            .curtain-panel,
            .curtain-panel-image,
            .curtain-open-copy,
            .curtain-tap-hint {
                transition-duration: 0.01ms !important;
                transition-delay: 0ms !important;
            }

            .curtain-is-opening .curtain-panel-left,
            .curtain-is-opening .curtain-panel-right {
                transform: none;
                opacity: 0;
            }
        }
    </style>

    <div
        class="curtain-photo-stage"
        id="curtain-photo-stage"
        wire:ignore
    >
        <button
            type="button"
            class="curtain-photo-trigger"
            id="curtain-photo-trigger"
            aria-label="{{ __('invitation.envelope_open') }}"
            aria-expanded="false"
        >
            <span class="curtain-photo-media" aria-hidden="true">
                <img
                    class="curtain-photo-image curtain-photo-open"
                    src="{{ $curtainOpenUrl }}"
                    alt=""
                    loading="eager"
                    decoding="async"
                >

                <span class="curtain-panels">
                    <span class="curtain-panel curtain-panel-left">
                        <img
                            class="curtain-panel-image"
                            src="{{ $curtainClosedUrl }}"
                            alt=""
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        >
                    </span>
                    <span class="curtain-panel curtain-panel-right">
                        <img
                            class="curtain-panel-image"
                            src="{{ $curtainClosedUrl }}"
                            alt=""
                            loading="eager"
                            decoding="async"
                        >
                    </span>
                </span>
            </span>

            <span class="curtain-center-light" aria-hidden="true"></span>
            <span class="curtain-photo-particles" id="curtain-photo-particles" aria-hidden="true"></span>

            <span class="curtain-open-copy">
                <span class="curtain-open-ornament" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 21c-5.4-3.65-8.8-6.5-8.8-10.45A4.55 4.55 0 0 1 12 8.92a4.55 4.55 0 0 1 8.8 1.63C20.8 14.5 17.4 17.35 12 21Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="curtain-open-eyebrow">{{ __('invitation.save_the_date') }}</span>
                <span class="curtain-open-names">{{ $event->couple_names }}</span>
                <span class="curtain-open-rule" aria-hidden="true"></span>
                @if ($curtainMeta)
                    <span class="curtain-open-meta">{{ $curtainMeta }}</span>
                @endif
            </span>

            <span class="curtain-tap-hint">{{ __('invitation.envelope_tap') }}</span>
        </button>
    </div>

    <script>
        (function () {
            const stage = document.getElementById('curtain-photo-stage');
            const trigger = document.getElementById('curtain-photo-trigger');
            const particleLayer = document.getElementById('curtain-photo-particles');

            if (!stage || !trigger) {
                return;
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const css = getComputedStyle(document.documentElement);
            const CROSSFADE = parseInt(css.getPropertyValue('--curtain-crossfade'), 10) || 1350;
            const HOLD = parseInt(css.getPropertyValue('--curtain-hold'), 10) || 900;
            const ZOOM = parseInt(css.getPropertyValue('--curtain-zoom'), 10) || 900;
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

            function spawnDust(index) {
                if (!particleLayer || particleLayer.childElementCount >= 22) {
                    return;
                }

                const particle = document.createElement('span');
                particle.className = 'curtain-photo-particle';
                particle.style.setProperty('--size', (2 + Math.random() * 5).toFixed(1) + 'px');
                particle.style.setProperty('--dx', (Math.random() * 240 - 120).toFixed(0) + 'px');
                particle.style.setProperty('--dy', (Math.random() * 200 - 130).toFixed(0) + 'px');
                particle.style.setProperty('--scale', (0.5 + Math.random() * 1.1).toFixed(2));
                particle.style.setProperty('--duration', (0.85 + Math.random() * 0.75).toFixed(2) + 's');
                particle.style.background = index % 4 === 0 ? '#fff7dc' : '#e4bc70';

                particleLayer.appendChild(particle);
                particle.addEventListener('animationend', () => particle.remove(), { once: true });
            }

            function releaseDust() {
                for (let index = 0; index < 20; index++) {
                    window.setTimeout(() => spawnDust(index), index * 30);
                }
            }

            function reveal() {
                if (started) {
                    return;
                }

                started = true;
                trigger.setAttribute('aria-expanded', 'true');

                window.envYtPlayOnGesture?.();

                stage.classList.add('curtain-is-opening');

                if (reduceMotion) {
                    window.setTimeout(showInviteContent, 180);
                    window.setTimeout(() => {
                        stage.classList.add('curtain-is-fading');
                        finishReveal();
                    }, 420);
                    return;
                }

                window.setTimeout(releaseDust, 140);

                window.setTimeout(() => {
                    stage.classList.add('curtain-is-zooming');
                }, CROSSFADE + HOLD);

                window.setTimeout(showInviteContent, CROSSFADE + HOLD + 170);

                window.setTimeout(() => {
                    stage.classList.add('curtain-is-fading');
                    finishReveal();
                }, CROSSFADE + HOLD + ZOOM);
            }

            trigger.addEventListener('click', reveal, { once: true });
        })();
    </script>
@endif

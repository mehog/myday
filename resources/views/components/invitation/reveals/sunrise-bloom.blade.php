@if (! $isPreview)
    @php
        $bloomDate = $event->wedding_date->format('d · m · Y');
        $bloomMeta = collect([$bloomDate, $event->location_name])->filter()->implode(' — ');

        $bloomAccents = [
            'amber-gold' => ['ink' => '#332516', 'gold' => '#b88b42', 'blossom' => '#e8b4a8', 'sky' => '#fef6ee'],
            'royal-wedding' => ['ink' => '#172944', 'gold' => '#b18b32', 'blossom' => '#d4c0e8', 'sky' => '#f8f4fc'],
            'lavender-dream' => ['ink' => '#463653', 'gold' => '#9b7bb8', 'blossom' => '#e0c8e8', 'sky' => '#faf6fc'],
            'winter-magic' => ['ink' => '#20364b', 'gold' => '#6299ba', 'blossom' => '#b8d8e8', 'sky' => '#f4f8fc'],
            'pearl-white' => ['ink' => '#33291f', 'gold' => '#8C7355', 'blossom' => '#ead8c8', 'sky' => '#fdfaf6'],
            'dusty-rose' => ['ink' => '#532f2c', 'gold' => '#B5706A', 'blossom' => '#e8b8b0', 'sky' => '#fdf6f4'],
            'paper-ink' => ['ink' => '#3A2E24', 'gold' => '#9A7B4F', 'blossom' => '#dcc8a8', 'sky' => '#faf8f4'],
        ];

        $bloomAccent = $bloomAccents[$event->theme->value] ?? $bloomAccents['amber-gold'];
        $bloomClosedUrl = asset('img/sunrise-bloom-reveal/nasdan-sunrise-bloom-closed.webp');
        $bloomOpenUrl = asset('img/sunrise-bloom-reveal/nasdan-sunrise-bloom-open.webp');
    @endphp

    <style>
        :root {
            --bloom-crossfade: 1350;
            --bloom-hold: 900;
            --bloom-zoom: 900;
            --bloom-ink: {{ $bloomAccent['ink'] }};
            --bloom-gold: {{ $bloomAccent['gold'] }};
            --bloom-blossom: {{ $bloomAccent['blossom'] }};
            --bloom-sky: {{ $bloomAccent['sky'] }};
        }

        .bloom-photo-stage,
        .bloom-photo-stage * {
            box-sizing: border-box;
        }

        .bloom-photo-stage {
            --bloom-closed-image: url('{{ $bloomClosedUrl }}');
            position: fixed;
            inset: 0;
            z-index: 100;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: hidden;
            isolation: isolate;
            background: var(--bloom-sky);
            opacity: 1;
            transition: opacity 0.78s ease, filter 0.78s ease;
        }

        .bloom-photo-stage::before {
            content: '';
            position: absolute;
            z-index: -2;
            inset: -28px;
            background-image: var(--bloom-closed-image);
            background-position: center;
            background-size: cover;
            filter: blur(22px) saturate(0.88) brightness(0.94);
            transform: scale(1.08);
        }

        .bloom-photo-stage::after {
            content: '';
            position: absolute;
            z-index: 8;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 46%, rgba(255, 228, 196, 0.52), transparent 36%);
            opacity: 0;
            transition: opacity 0.55s ease;
        }

        .bloom-photo-stage.bloom-is-opening::after {
            animation: bloom-stage-bloom 1.25s ease-out both;
        }

        .bloom-photo-stage.bloom-is-fading {
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            pointer-events: none;
        }

        @keyframes bloom-stage-bloom {
            0%, 100% { opacity: 0; }
            35% { opacity: 0.68; }
        }

        .bloom-photo-trigger {
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

        .bloom-photo-trigger:focus-visible {
            outline: 2px solid rgba(184, 139, 66, 0.95);
            outline-offset: -8px;
        }

        .bloom-photo-trigger[aria-expanded='true'] {
            cursor: default;
            pointer-events: none;
        }

        .bloom-photo-media {
            position: absolute;
            inset: 0;
            overflow: hidden;
            background: var(--bloom-sky);
            transform: scale(1);
            transform-origin: 50% 46%;
            transition: transform 0.95s cubic-bezier(0.65, 0, 0.35, 1), filter 0.7s ease;
            will-change: transform, filter;
        }

        .bloom-photo-media::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 4;
            pointer-events: none;
            background:
                linear-gradient(180deg, rgba(255, 248, 240, 0.14), transparent 24%),
                radial-gradient(ellipse at center, transparent 54%, rgba(255, 240, 228, 0.18) 100%);
        }

        .bloom-photo-image {
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

        .bloom-photo-open {
            z-index: 1;
            opacity: 0;
            filter: brightness(1.06) blur(2px);
            transform: scale(1.03);
            transition:
                opacity 1.15s cubic-bezier(0.4, 0, 0.2, 1) 0.18s,
                transform 1.55s cubic-bezier(0.16, 1, 0.3, 1) 0.12s,
                filter 1.05s ease 0.12s;
        }

        .bloom-photo-closed.bloom-veil {
            z-index: 2;
            -webkit-mask-image: radial-gradient(circle at 50% 46%, #000 0%, #000 38%, transparent 39%);
            mask-image: radial-gradient(circle at 50% 46%, #000 0%, #000 38%, transparent 39%);
            -webkit-mask-size: 100% 100%;
            mask-size: 100% 100%;
            -webkit-mask-repeat: no-repeat;
            mask-repeat: no-repeat;
            transition:
                opacity 1.05s ease,
                filter 1s ease,
                transform 0.35s ease,
                -webkit-mask-size 1.35s cubic-bezier(0.4, 0, 0.2, 1),
                mask-size 1.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .bloom-is-pressing .bloom-photo-closed.bloom-veil {
            transform: scale(0.988);
            filter: brightness(0.96) saturate(1.04);
        }

        .bloom-petals {
            position: absolute;
            inset: 0;
            z-index: 3;
            pointer-events: none;
        }

        .bloom-petal {
            position: absolute;
            left: 50%;
            top: 46%;
            width: clamp(88px, 24vw, 168px);
            height: clamp(110px, 28vw, 200px);
            margin-left: calc(clamp(88px, 24vw, 168px) / -2);
            margin-top: calc(clamp(110px, 28vw, 200px) / -2);
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            background: color-mix(in srgb, var(--bloom-blossom) 72%, white);
            opacity: 0.72;
            transform-origin: center bottom;
            transition:
                transform 1.05s cubic-bezier(0.55, 0.02, 0.18, 1),
                opacity 0.75s ease 0.2s;
            box-shadow: inset 0 -8px 18px rgba(255, 255, 255, 0.35);
        }

        .bloom-petal-1 { transform: rotate(0deg) translateY(0); }
        .bloom-petal-2 { transform: rotate(90deg) translateY(0); }
        .bloom-petal-3 { transform: rotate(180deg) translateY(0); }
        .bloom-petal-4 { transform: rotate(270deg) translateY(0); }

        .bloom-is-opening .bloom-petal-1 { transform: rotate(0deg) translateY(-120%) scale(0.92); opacity: 0; }
        .bloom-is-opening .bloom-petal-2 { transform: rotate(90deg) translateY(-120%) scale(0.92); opacity: 0; }
        .bloom-is-opening .bloom-petal-3 { transform: rotate(180deg) translateY(-120%) scale(0.92); opacity: 0; }
        .bloom-is-opening .bloom-petal-4 { transform: rotate(270deg) translateY(-120%) scale(0.92); opacity: 0; }

        .bloom-vines {
            position: absolute;
            z-index: 5;
            left: 50%;
            top: 46%;
            width: min(78vw, 340px);
            height: min(78vw, 340px);
            transform: translate(-50%, -50%);
            pointer-events: none;
            overflow: visible;
        }

        .bloom-vine-line {
            fill: none;
            stroke: color-mix(in srgb, var(--bloom-gold) 55%, var(--bloom-blossom));
            stroke-width: 1.6;
            stroke-linecap: round;
            stroke-dasharray: 140;
            stroke-dashoffset: 140;
            opacity: 0.82;
        }

        .bloom-is-opening .bloom-vine-line {
            animation: bloom-vine-draw 1s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .bloom-is-opening .bloom-vine-line:nth-child(1) { animation-delay: 0.06s; }
        .bloom-is-opening .bloom-vine-line:nth-child(2) { animation-delay: 0.14s; }
        .bloom-is-opening .bloom-vine-line:nth-child(3) { animation-delay: 0.22s; }
        .bloom-is-opening .bloom-vine-line:nth-child(4) { animation-delay: 0.3s; }

        @keyframes bloom-vine-draw {
            to { stroke-dashoffset: 0; }
        }

        .bloom-is-opening .bloom-veil {
            opacity: 0;
            -webkit-mask-size: 320% 320%;
            mask-size: 320% 320%;
        }

        .bloom-is-opening .bloom-photo-open {
            opacity: 1;
            filter: brightness(1) blur(0);
            transform: scale(1);
        }

        .bloom-is-zooming .bloom-photo-media {
            filter: brightness(1.1);
            transform: scale(1.16);
        }

        .bloom-sunrise-glow {
            position: absolute;
            z-index: 6;
            left: 50%;
            top: 46%;
            width: clamp(130px, 36vw, 270px);
            height: clamp(130px, 36vw, 270px);
            border-radius: 50%;
            opacity: 0;
            pointer-events: none;
            background: radial-gradient(
                ellipse at center,
                rgba(255, 236, 200, 0.82) 0%,
                rgba(255, 200, 168, 0.32) 38%,
                transparent 72%
            );
            transform: translate(-50%, -50%) scale(0.4);
        }

        .bloom-is-opening .bloom-sunrise-glow {
            animation: bloom-sunrise-glow 1.15s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
        }

        @keyframes bloom-sunrise-glow {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.4); }
            28% { opacity: 0.95; }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.4); }
        }

        .bloom-open-copy {
            position: absolute;
            z-index: 5;
            left: 50%;
            top: 31%;
            width: min(70vw, 520px);
            color: var(--bloom-ink);
            text-align: center;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.72);
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, 28px) scale(0.97);
            transition:
                opacity 0.9s ease 0.72s,
                transform 1.05s cubic-bezier(0.16, 1, 0.3, 1) 0.7s,
                filter 0.8s ease 0.7s;
            pointer-events: none;
        }

        .bloom-is-opening .bloom-open-copy {
            opacity: 1;
            filter: blur(0);
            transform: translate(-50%, 0) scale(1);
        }

        .bloom-is-zooming .bloom-open-copy {
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, -12px) scale(1.12);
            transition:
                opacity 0.52s ease,
                transform 0.85s cubic-bezier(0.65, 0, 0.35, 1),
                filter 0.55s ease;
        }

        .bloom-open-ornament {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(10px, 2.2vw, 18px);
            margin-bottom: clamp(12px, 2.2vh, 22px);
            color: var(--bloom-gold);
        }

        .bloom-open-ornament::before,
        .bloom-open-ornament::after {
            content: '';
            width: clamp(34px, 10vw, 84px);
            height: 1px;
            background: linear-gradient(90deg, transparent, currentColor);
        }

        .bloom-open-ornament::after {
            background: linear-gradient(90deg, currentColor, transparent);
        }

        .bloom-open-ornament svg {
            width: clamp(18px, 3.6vw, 26px);
            height: auto;
        }

        .bloom-open-eyebrow,
        .bloom-open-names,
        .bloom-open-meta {
            display: block;
        }

        .bloom-open-eyebrow {
            margin-bottom: clamp(9px, 1.8vh, 16px);
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.2vw, 13px);
            font-weight: 500;
            line-height: 1.35;
            letter-spacing: clamp(3px, 0.8vw, 6px);
            text-transform: uppercase;
            color: var(--bloom-gold);
        }

        .bloom-open-names {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(34px, 9vw, 76px);
            font-weight: 600;
            font-style: italic;
            line-height: 1.03;
            text-wrap: balance;
        }

        .bloom-open-rule {
            display: block;
            width: clamp(62px, 16vw, 120px);
            height: 1px;
            margin: clamp(15px, 2.5vh, 24px) auto clamp(11px, 2vh, 18px);
            background: linear-gradient(90deg, transparent, var(--bloom-gold), transparent);
        }

        .bloom-open-meta {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(8px, 2vw, 12px);
            font-weight: 500;
            line-height: 1.55;
            letter-spacing: clamp(1.3px, 0.45vw, 3px);
            text-transform: uppercase;
            color: color-mix(in srgb, var(--bloom-ink) 72%, white);
        }

        .bloom-tap-hint {
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
            border: 1px solid rgba(184, 139, 66, 0.2);
            border-radius: 999px;
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.4vw, 12px);
            font-weight: 500;
            line-height: 1;
            letter-spacing: clamp(3px, 0.9vw, 6px);
            text-transform: uppercase;
            white-space: nowrap;
            color: rgba(68, 48, 32, 0.82);
            background: rgba(255, 248, 240, 0.72);
            box-shadow: 0 8px 28px rgba(184, 139, 66, 0.12);
            backdrop-filter: blur(8px);
            transform: translateX(-50%);
            transition: opacity 0.4s ease, transform 0.5s ease;
        }

        .bloom-tap-hint::before {
            content: '';
            width: 6px;
            height: 6px;
            flex: 0 0 auto;
            border-radius: 50%;
            background: var(--bloom-gold);
            animation: bloom-tap-pulse 1.9s ease-out infinite;
        }

        .bloom-is-pressing .bloom-tap-hint,
        .bloom-is-opening .bloom-tap-hint {
            opacity: 0;
            transform: translate(-50%, 12px);
        }

        @keyframes bloom-tap-pulse {
            0% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--bloom-gold) 45%, transparent); }
            100% { box-shadow: 0 0 0 10px transparent; }
        }

        .bloom-photo-particles {
            position: absolute;
            z-index: 7;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .bloom-petal-particle {
            position: absolute;
            left: 50%;
            top: 46%;
            width: var(--size);
            height: calc(var(--size) * 1.4);
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            background: var(--bloom-blossom);
            opacity: 0;
            animation: bloom-petal-rise var(--duration) ease-out forwards;
        }

        @keyframes bloom-petal-rise {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.2) rotate(0deg); }
            18% { opacity: 0.88; }
            100% {
                opacity: 0;
                transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(var(--scale)) rotate(var(--rot));
            }
        }

        @media (orientation: landscape) {
            .bloom-photo-image {
                object-fit: contain;
            }

            .bloom-open-copy {
                top: 22%;
                width: min(38vw, 460px);
            }

            .bloom-open-names {
                font-size: clamp(30px, 5vw, 58px);
            }

            .bloom-tap-hint {
                bottom: max(5vh, calc(env(safe-area-inset-bottom) + 18px));
            }
        }

        @media (max-width: 430px) and (min-height: 760px) {
            .bloom-open-copy {
                top: 28%;
                width: 72vw;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .bloom-tap-hint::before,
            .bloom-petal-particle,
            .bloom-is-opening .bloom-sunrise-glow,
            .bloom-photo-stage.bloom-is-opening::after,
            .bloom-is-opening .bloom-vine-line {
                animation: none;
            }

            .bloom-photo-stage,
            .bloom-photo-media,
            .bloom-photo-image,
            .bloom-petal,
            .bloom-open-copy,
            .bloom-tap-hint {
                transition-duration: 0.01ms !important;
                transition-delay: 0ms !important;
            }

            .bloom-is-opening .bloom-veil {
                opacity: 0;
            }

            .bloom-is-opening .bloom-vine-line {
                stroke-dashoffset: 0;
            }

            .bloom-is-opening .bloom-petal {
                opacity: 0;
            }
        }
    </style>

    <div
        class="bloom-photo-stage"
        id="bloom-photo-stage"
        wire:ignore
    >
        <button
            type="button"
            class="bloom-photo-trigger"
            id="bloom-photo-trigger"
            aria-label="{{ __('invitation.envelope_open') }}"
            aria-expanded="false"
        >
            <span class="bloom-photo-media" aria-hidden="true">
                <img
                    class="bloom-photo-image bloom-photo-open"
                    src="{{ $bloomOpenUrl }}"
                    alt=""
                    loading="eager"
                    decoding="async"
                >

                <img
                    class="bloom-photo-image bloom-photo-closed bloom-veil"
                    src="{{ $bloomClosedUrl }}"
                    alt=""
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                >

                <span class="bloom-petals" aria-hidden="true">
                    <span class="bloom-petal bloom-petal-1"></span>
                    <span class="bloom-petal bloom-petal-2"></span>
                    <span class="bloom-petal bloom-petal-3"></span>
                    <span class="bloom-petal bloom-petal-4"></span>
                </span>

                <svg class="bloom-vines" viewBox="0 0 400 400" aria-hidden="true">
                    <path class="bloom-vine-line" d="M200 200 C200 160, 180 130, 160 110"/>
                    <path class="bloom-vine-line" d="M200 200 C200 160, 220 130, 240 110"/>
                    <path class="bloom-vine-line" d="M200 200 C240 200, 270 220, 290 240"/>
                    <path class="bloom-vine-line" d="M200 200 C160 200, 130 220, 110 240"/>
                </svg>
            </span>

            <span class="bloom-sunrise-glow" aria-hidden="true"></span>
            <span class="bloom-photo-particles" id="bloom-photo-particles" aria-hidden="true"></span>

            <span class="bloom-open-copy">
                <span class="bloom-open-ornament" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 18c-3.2-2.4-5.2-4.4-5.2-7.2a3.2 3.2 0 0 1 5.2-2.5 3.2 3.2 0 0 1 5.2 2.5C17.2 13.6 15.2 15.6 12 18Z" fill="currentColor" opacity="0.35"/>
                        <circle cx="12" cy="8.5" r="2.2" fill="currentColor"/>
                        <path d="M8 14c1.2 1.6 2.4 2.4 4 2.4s2.8-.8 4-2.4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="bloom-open-eyebrow">{{ __('invitation.save_the_date') }}</span>
                <span class="bloom-open-names">{{ $event->couple_names }}</span>
                <span class="bloom-open-rule" aria-hidden="true"></span>
                @if ($bloomMeta)
                    <span class="bloom-open-meta">{{ $bloomMeta }}</span>
                @endif
            </span>

            <span class="bloom-tap-hint">{{ __('invitation.envelope_tap') }}</span>
        </button>
    </div>

    <script>
        (function () {
            const stage = document.getElementById('bloom-photo-stage');
            const trigger = document.getElementById('bloom-photo-trigger');
            const particleLayer = document.getElementById('bloom-photo-particles');

            if (!stage || !trigger) {
                return;
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const css = getComputedStyle(document.documentElement);
            const CROSSFADE = parseInt(css.getPropertyValue('--bloom-crossfade'), 10) || 1350;
            const HOLD = parseInt(css.getPropertyValue('--bloom-hold'), 10) || 900;
            const ZOOM = parseInt(css.getPropertyValue('--bloom-zoom'), 10) || 900;
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

            function spawnPetal(index) {
                if (!particleLayer || particleLayer.childElementCount >= 22) {
                    return;
                }

                const particle = document.createElement('span');
                particle.className = 'bloom-petal-particle';
                particle.style.setProperty('--size', (4 + Math.random() * 6).toFixed(1) + 'px');
                particle.style.setProperty('--dx', (Math.random() * 140 - 70).toFixed(0) + 'px');
                particle.style.setProperty('--dy', (Math.random() * -160 - 30).toFixed(0) + 'px');
                particle.style.setProperty('--scale', (0.6 + Math.random() * 0.9).toFixed(2));
                particle.style.setProperty('--rot', (Math.random() * 120 - 60).toFixed(0) + 'deg');
                particle.style.setProperty('--duration', (0.9 + Math.random() * 0.9).toFixed(2) + 's');

                const palette = ['#e8b4a8', '#f0d0c0', '#f8e8dc', '#dcc8a8'];
                particle.style.background = palette[index % palette.length];

                particleLayer.appendChild(particle);
                particle.addEventListener('animationend', () => particle.remove(), { once: true });
            }

            function releasePetals() {
                for (let index = 0; index < 20; index++) {
                    window.setTimeout(() => spawnPetal(index), index * 30);
                }
            }

            function beginOpening() {
                stage.classList.remove('bloom-is-pressing');
                stage.classList.add('bloom-is-opening');

                if (reduceMotion) {
                    window.setTimeout(showInviteContent, 180);
                    window.setTimeout(() => {
                        stage.classList.add('bloom-is-fading');
                        finishReveal();
                    }, 420);
                    return;
                }

                window.setTimeout(releasePetals, 160);

                window.setTimeout(() => {
                    stage.classList.add('bloom-is-zooming');
                }, CROSSFADE + HOLD);

                window.setTimeout(showInviteContent, CROSSFADE + HOLD + 170);

                window.setTimeout(() => {
                    stage.classList.add('bloom-is-fading');
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

                stage.classList.add('bloom-is-pressing');

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

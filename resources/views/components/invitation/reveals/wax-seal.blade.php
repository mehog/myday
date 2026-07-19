@if (! $isPreview)
    @php
        $sealDate = $event->wedding_date->format('d · m · Y');
        $sealMeta = collect([$sealDate, $event->location_name])->filter()->implode(' — ');

        $sealAccents = [
            'amber-gold' => ['ink' => '#332516', 'gold' => '#b88b42', 'wax' => '#a83b57'],
            'royal-wedding' => ['ink' => '#172944', 'gold' => '#b18b32', 'wax' => '#8b3a4a'],
            'lavender-dream' => ['ink' => '#463653', 'gold' => '#9b7bb8', 'wax' => '#7a5a8a'],
            'winter-magic' => ['ink' => '#20364b', 'gold' => '#6299ba', 'wax' => '#4a7a9a'],
            'pearl-white' => ['ink' => '#33291f', 'gold' => '#8C7355', 'wax' => '#8C7355'],
            'dusty-rose' => ['ink' => '#532f2c', 'gold' => '#B5706A', 'wax' => '#9A5A55'],
            'paper-ink' => ['ink' => '#3A2E24', 'gold' => '#9A7B4F', 'wax' => '#7A623E'],
        ];

        $sealAccent = $sealAccents[$event->theme->value] ?? $sealAccents['amber-gold'];
        $sealClosedUrl = asset('img/wax-seal-reveal/nasdan-wax-seal-closed.webp');
        $sealOpenUrl = asset('img/wax-seal-reveal/nasdan-wax-seal-open.webp');
    @endphp

    <style>
        :root {
            --seal-crossfade: 1350;
            --seal-hold: 900;
            --seal-zoom: 900;
            --seal-ink: {{ $sealAccent['ink'] }};
            --seal-gold: {{ $sealAccent['gold'] }};
            --seal-wax: {{ $sealAccent['wax'] }};
            --seal-wax-light: color-mix(in srgb, var(--seal-wax), #ffffff 38%);
            --seal-wax-dark: color-mix(in srgb, var(--seal-wax), #000000 32%);
        }

        .seal-photo-stage,
        .seal-photo-stage * {
            box-sizing: border-box;
        }

        .seal-photo-stage {
            --seal-closed-image: url('{{ $sealClosedUrl }}');
            position: fixed;
            inset: 0;
            z-index: 100;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: hidden;
            isolation: isolate;
            background: #120c0a;
            opacity: 1;
            transition: opacity 0.78s ease, filter 0.78s ease;
        }

        .seal-photo-stage::before {
            content: '';
            position: absolute;
            z-index: -2;
            inset: -28px;
            background-image: var(--seal-closed-image);
            background-position: center;
            background-size: cover;
            filter: blur(22px) saturate(0.72) brightness(0.88);
            transform: scale(1.08);
        }

        .seal-photo-stage::after {
            content: none;
        }

        .seal-photo-stage.seal-is-opening::after {
            animation: none;
        }

        .seal-photo-stage.seal-is-fading {
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            pointer-events: none;
        }

        @keyframes seal-stage-bloom {
            0%, 100% { opacity: 0; }
            36% { opacity: 0.58; }
        }

        .seal-photo-trigger {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            margin: 0;
            padding:
                max(0px, env(safe-area-inset-top))
                max(0px, env(safe-area-inset-right))
                max(0px, env(safe-area-inset-bottom))
                max(0px, env(safe-area-inset-left));
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

        .seal-photo-canvas {
            position: relative;
            width: min(577px, 100vw, calc(100dvh * 9 / 16));
            aspect-ratio: 9 / 16;
            max-height: calc(100dvh - env(safe-area-inset-top) - env(safe-area-inset-bottom));
            container-type: inline-size;
            container-name: seal-canvas;
            overflow: hidden;
            isolation: isolate;
            background: #f1e9dd;
            box-shadow:
                0 24px 60px rgba(80, 55, 30, 0.12),
                0 0 0 1px rgba(121, 89, 48, 0.08);
        }

        .seal-photo-canvas::after {
            content: '';
            position: absolute;
            z-index: 8;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 44%, rgba(255, 248, 228, 0.55), transparent 34%);
            opacity: 0;
            transition: opacity 0.55s ease;
        }

        .seal-photo-stage.seal-is-opening .seal-photo-canvas::after {
            animation: seal-stage-bloom 1.2s ease-out both;
        }

        .seal-photo-trigger:focus-visible {
            outline: none;
        }

        .seal-photo-trigger:focus-visible .seal-photo-canvas {
            outline: 2px solid rgba(184, 139, 66, 0.95);
            outline-offset: -4px;
        }

        .seal-photo-trigger[aria-expanded='true'] {
            cursor: default;
            pointer-events: none;
        }

        .seal-photo-media {
            position: absolute;
            inset: 0;
            overflow: hidden;
            background: #f1e9dd;
            transform: scale(1);
            transform-origin: 50% 44%;
            transition: transform 0.95s cubic-bezier(0.65, 0, 0.35, 1), filter 0.7s ease;
            will-change: transform, filter;
        }

        .seal-photo-media::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 4;
            pointer-events: none;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.07), transparent 24%),
                radial-gradient(ellipse at center, transparent 54%, rgba(40, 24, 16, 0.14) 100%);
        }

        .seal-photo-image {
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

        .seal-photo-closed {
            z-index: 2;
            opacity: 1;
            filter: brightness(1);
            transform: scale(1);
            transition:
                opacity 1.05s cubic-bezier(0.4, 0, 0.2, 1),
                transform 1.45s cubic-bezier(0.2, 0.75, 0.22, 1),
                filter 1s ease;
        }

        .seal-photo-open {
            z-index: 1;
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            transform: scale(1.035);
            transition:
                opacity 1.2s cubic-bezier(0.4, 0, 0.2, 1) 0.14s,
                transform 1.65s cubic-bezier(0.16, 1, 0.3, 1) 0.1s,
                filter 1.1s ease 0.1s;
        }

        .seal-is-opening .seal-photo-closed {
            opacity: 0;
            filter: brightness(1.1) blur(2px);
            transform: scale(1.02);
        }

        .seal-is-opening .seal-photo-open {
            opacity: 1;
            filter: brightness(1) blur(0);
            transform: scale(1);
        }

        .seal-is-zooming .seal-photo-media {
            filter: brightness(1.09);
            transform: scale(1.16);
        }

        .seal-fx-layer {
            position: absolute;
            inset: 0;
            z-index: 5;
            pointer-events: none;
        }

        .seal-ribbon-fx {
            position: absolute;
            left: 50%;
            top: 46%;
            z-index: 1;
            pointer-events: none;
            background: linear-gradient(
                180deg,
                color-mix(in srgb, var(--seal-gold) 72%, white),
                color-mix(in srgb, var(--seal-gold) 55%, transparent) 45%,
                color-mix(in srgb, var(--seal-gold) 38%, black)
            );
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
            transition: opacity 0.45s ease 0.12s, transform 0.55s cubic-bezier(0.5, 0.05, 0.2, 1) 0.12s;
        }

        .seal-ribbon-fx-h {
            width: clamp(120px, 34cqw, 220px);
            height: 3px;
            transform: translate(-50%, -50%);
        }

        .seal-ribbon-fx-v {
            width: 3px;
            height: clamp(120px, 34cqw, 220px);
            transform: translate(-50%, -50%);
        }

        .seal-is-opening .seal-ribbon-fx {
            opacity: 0;
        }

        .seal-is-opening .seal-ribbon-fx-h {
            transform: translate(-50%, -50%) scaleX(0.72);
        }

        .seal-is-opening .seal-ribbon-fx-v {
            transform: translate(-50%, -50%) scaleY(0.72);
        }

        .seal-wax-overlay {
            position: absolute;
            left: 50%;
            top: 46%;
            width: clamp(62px, 16cqw, 92px);
            height: clamp(62px, 16cqw, 92px);
            transform: translate(-50%, -50%);
            z-index: 2;
            filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.38));
            transition: transform 0.18s ease, opacity 0.35s ease 0.45s;
        }

        .seal-is-pressing .seal-wax-overlay {
            transform: translate(-50%, -50%) scale(0.92);
        }

        .seal-is-opening .seal-wax-overlay {
            opacity: 0;
        }

        .seal-wax-surface {
            background: radial-gradient(
                ellipse 70% 55% at 48% 22%,
                var(--seal-wax-light) 0%,
                var(--seal-wax) 65%,
                var(--seal-wax-dark) 100%
            );
            box-shadow:
                inset 0 1px 2px rgba(255, 255, 255, 0.22),
                inset 0 -3px 5px rgba(0, 0, 0, 0.28);
        }

        .seal-wax-base {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            clip-path: polygon(
                50% 0%, 62% 8%, 74% 4%, 84% 14%, 96% 12%, 92% 26%,
                100% 38%, 90% 48%, 96% 62%, 84% 68%, 82% 84%, 68% 88%,
                62% 100%, 50% 92%, 38% 100%, 32% 88%, 18% 84%, 16% 68%,
                4% 62%, 10% 48%, 0% 38%, 8% 26%, 4% 12%, 16% 14%, 26% 4%, 38% 8%
            );
            transition: opacity 0.2s ease;
        }

        .seal-wax-half {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            opacity: 0;
            transition:
                transform 0.55s cubic-bezier(0.4, 0.1, 0.3, 1) 0.14s,
                opacity 0.45s ease 0.14s;
        }

        .seal-wax-left {
            clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);
        }

        .seal-wax-right {
            clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%);
        }

        .seal-wax-emblem {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
        }

        .seal-wax-emblem path {
            fill: rgba(0, 0, 0, 0.26);
            filter: drop-shadow(0 1px 0 rgba(255, 255, 255, 0.12));
        }

        .seal-crack-line {
            position: absolute;
            inset: 0;
            z-index: 3;
            pointer-events: none;
        }

        .seal-crack-line path {
            fill: none;
            stroke: rgba(255, 248, 220, 0.95);
            stroke-width: 2.2;
            stroke-linecap: round;
            stroke-linejoin: round;
            filter: drop-shadow(0 0 3px rgba(255, 240, 190, 0.8));
            stroke-dasharray: 120;
            stroke-dashoffset: 120;
        }

        .seal-is-opening .seal-crack-line path {
            animation: seal-crack-draw 0.55s ease forwards;
        }

        @keyframes seal-crack-draw {
            to { stroke-dashoffset: 0; }
        }

        .seal-is-opening .seal-wax-base {
            opacity: 0;
        }

        .seal-is-opening .seal-wax-half {
            opacity: 1;
        }

        .seal-is-opening .seal-wax-emblem {
            opacity: 0;
            transition: opacity 0.2s ease 0.08s;
        }

        .seal-is-opening .seal-wax-left {
            transform: translate(-14px, 10px) rotate(-14deg);
            opacity: 0;
        }

        .seal-is-opening .seal-wax-right {
            transform: translate(14px, 10px) rotate(14deg);
            opacity: 0;
        }

        .seal-center-light {
            position: absolute;
            z-index: 6;
            left: 50%;
            top: 44%;
            width: clamp(100px, 28cqw, 220px);
            aspect-ratio: 1;
            border-radius: 50%;
            opacity: 0;
            pointer-events: none;
            background: radial-gradient(
                circle at center,
                rgba(255, 248, 220, 0.68) 0%,
                rgba(255, 228, 160, 0.24) 42%,
                transparent 72%
            );
            transform: translate(-50%, -50%) scale(0.35);
        }

        .seal-is-opening .seal-center-light {
            animation: seal-center-bloom 1.05s cubic-bezier(0.16, 1, 0.3, 1) 0.22s both;
        }

        @keyframes seal-center-bloom {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.35); }
            30% { opacity: 0.92; }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.55); }
        }

        .seal-open-copy {
            position: absolute;
            z-index: 5;
            left: 50%;
            top: 30%;
            width: min(70cqw, 520px);
            color: var(--seal-ink);
            text-align: center;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.65);
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, 28px) scale(0.97);
            transition:
                opacity 0.9s ease 0.74s,
                transform 1.05s cubic-bezier(0.16, 1, 0.3, 1) 0.72s,
                filter 0.8s ease 0.72s;
            pointer-events: none;
        }

        .seal-is-opening .seal-open-copy {
            opacity: 1;
            filter: blur(0);
            transform: translate(-50%, 0) scale(1);
        }

        .seal-is-zooming .seal-open-copy {
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, -12px) scale(1.12);
            transition:
                opacity 0.52s ease,
                transform 0.85s cubic-bezier(0.65, 0, 0.35, 1),
                filter 0.55s ease;
        }

        .seal-open-ornament {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(10px, 2.2cqw, 18px);
            margin-bottom: clamp(12px, 2.2cqh, 22px);
            color: var(--seal-gold);
        }

        .seal-open-ornament::before,
        .seal-open-ornament::after {
            content: '';
            width: clamp(34px, 10cqw, 84px);
            height: 1px;
            background: linear-gradient(90deg, transparent, currentColor);
        }

        .seal-open-ornament::after {
            background: linear-gradient(90deg, currentColor, transparent);
        }

        .seal-open-ornament svg {
            width: clamp(15px, 3.2cqw, 23px);
            height: auto;
        }

        .seal-open-eyebrow,
        .seal-open-names,
        .seal-open-meta {
            display: block;
        }

        .seal-open-eyebrow {
            margin-bottom: clamp(9px, 1.8cqh, 16px);
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.2cqw, 13px);
            font-weight: 500;
            line-height: 1.35;
            letter-spacing: clamp(3px, 0.8cqw, 6px);
            text-transform: uppercase;
            color: var(--seal-gold);
        }

        .seal-open-names {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(34px, 9cqw, 76px);
            font-weight: 600;
            font-style: italic;
            line-height: 1.03;
            text-wrap: balance;
        }

        .seal-open-rule {
            display: block;
            width: clamp(62px, 16cqw, 120px);
            height: 1px;
            margin: clamp(15px, 2.5cqh, 24px) auto clamp(11px, 2cqh, 18px);
            background: linear-gradient(90deg, transparent, var(--seal-gold), transparent);
        }

        .seal-open-meta {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(8px, 2cqw, 12px);
            font-weight: 500;
            line-height: 1.55;
            letter-spacing: clamp(1.3px, 0.45cqw, 3px);
            text-transform: uppercase;
            color: color-mix(in srgb, var(--seal-ink) 72%, white);
        }

        .seal-tap-hint {
            position: absolute;
            z-index: 7;
            left: 50%;
            bottom: max(7%, calc(env(safe-area-inset-bottom) + 12px));
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: max-content;
            max-width: 88cqw;
            padding: clamp(10px, 2.4cqw, 12px) clamp(14px, 3.2cqw, 18px);
            border: 1px solid rgba(121, 89, 48, 0.18);
            border-radius: 999px;
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.4cqw, 12px);
            font-weight: 500;
            line-height: 1;
            letter-spacing: clamp(2px, 0.9cqw, 6px);
            text-transform: uppercase;
            white-space: nowrap;
            color: rgba(74, 51, 29, 0.77);
            background: rgba(255, 251, 244, 0.46);
            box-shadow: 0 8px 28px rgba(80, 55, 30, 0.08);
            backdrop-filter: blur(8px);
            transform: translateX(-50%);
            transition: opacity 0.4s ease, transform 0.5s ease;
        }

        .seal-tap-hint::before {
            content: '';
            width: 6px;
            height: 6px;
            flex: 0 0 auto;
            border-radius: 50%;
            background: var(--seal-gold);
            animation: seal-tap-pulse 1.9s ease-out infinite;
        }

        .seal-is-opening .seal-tap-hint,
        .seal-is-pressing .seal-tap-hint {
            opacity: 0;
            transform: translate(-50%, 12px);
        }

        @keyframes seal-tap-pulse {
            0% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--seal-gold) 45%, transparent); }
            100% { box-shadow: 0 0 0 10px transparent; }
        }

        .seal-photo-particles {
            position: absolute;
            z-index: 7;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .seal-photo-particle {
            position: absolute;
            left: 50%;
            top: 46%;
            width: var(--size);
            height: var(--size);
            border-radius: var(--radius, 50%);
            background: var(--color, #e4bc70);
            box-shadow: 0 0 8px color-mix(in srgb, var(--color, #e4bc70) 60%, transparent);
            opacity: 0;
            animation: seal-photo-dust var(--duration) ease-out forwards;
        }

        @keyframes seal-photo-dust {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.2) rotate(0deg); }
            18% { opacity: 0.92; }
            100% {
                opacity: 0;
                transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(var(--scale)) rotate(var(--rot));
            }
        }

        @container seal-canvas (max-width: 381px) {
            .seal-open-copy {
                width: 88%;
            }

            .seal-open-names {
                font-size: clamp(28px, 8.5cqw, 42px);
            }

            .seal-tap-hint {
                letter-spacing: clamp(1.5px, 0.7cqw, 4px);
                padding: 10px 14px;
            }

            .seal-wax-overlay {
                width: clamp(54px, 15cqw, 78px);
                height: clamp(54px, 15cqw, 78px);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .seal-tap-hint::before,
            .seal-photo-particle,
            .seal-is-opening .seal-center-light,
            .seal-is-opening .seal-crack-line path,
            .seal-photo-stage.seal-is-opening .seal-photo-canvas::after {
                animation: none;
            }

            .seal-photo-stage,
            .seal-photo-media,
            .seal-photo-image,
            .seal-wax-overlay,
            .seal-wax-half,
            .seal-ribbon-fx,
            .seal-open-copy,
            .seal-tap-hint,
            .seal-fx-layer {
                transition-duration: 0.01ms !important;
                transition-delay: 0ms !important;
            }

            .seal-is-opening .seal-wax-left,
            .seal-is-opening .seal-wax-right {
                transform: none;
                opacity: 0;
            }

            .seal-is-opening .seal-crack-line path {
                stroke-dashoffset: 0;
            }
        }
    </style>

    <div
        class="seal-photo-stage"
        id="seal-photo-stage"
        wire:ignore
    >
        <button
            type="button"
            class="seal-photo-trigger"
            id="seal-photo-trigger"
            aria-label="{{ __('invitation.envelope_open') }}"
            aria-expanded="false"
        >
            <span class="seal-photo-canvas">
            <span class="seal-photo-media" aria-hidden="true">
                <img
                    class="seal-photo-image seal-photo-open"
                    src="{{ $sealOpenUrl }}"
                    alt=""
                    loading="eager"
                    decoding="async"
                >
                <img
                    class="seal-photo-image seal-photo-closed"
                    src="{{ $sealClosedUrl }}"
                    alt=""
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                >
            </span>

            <span class="seal-fx-layer" aria-hidden="true">
                <span class="seal-ribbon-fx seal-ribbon-fx-h"></span>
                <span class="seal-ribbon-fx seal-ribbon-fx-v"></span>

                <span class="seal-wax-overlay">
                    <span class="seal-wax-base seal-wax-surface"></span>
                    <span class="seal-wax-half seal-wax-left seal-wax-surface"></span>
                    <span class="seal-wax-half seal-wax-right seal-wax-surface"></span>
                    <svg class="seal-wax-emblem" viewBox="0 0 76 76">
                        <path d="M38 52 C29 45 21 40 21 32 C21 26 28 23 32 28 C34 31 38 31 41 28 C45 23 52 26 52 32 C52 40 44 45 38 52 Z"/>
                    </svg>
                    <svg class="seal-crack-line" viewBox="0 0 76 76">
                        <path d="M38 6 L43 15 L33 24 L43 33 L33 42 L43 51 L34 60 L40 70"/>
                    </svg>
                </span>
            </span>

            <span class="seal-center-light" aria-hidden="true"></span>
            <span class="seal-photo-particles" id="seal-photo-particles" aria-hidden="true"></span>

            <span class="seal-open-copy">
                <span class="seal-open-ornament" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 21c-5.4-3.65-8.8-6.5-8.8-10.45A4.55 4.55 0 0 1 12 8.92a4.55 4.55 0 0 1 8.8 1.63C20.8 14.5 17.4 17.35 12 21Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="seal-open-eyebrow">{{ __('invitation.save_the_date') }}</span>
                <span class="seal-open-names">{{ $event->couple_names }}</span>
                <span class="seal-open-rule" aria-hidden="true"></span>
                @if ($sealMeta)
                    <span class="seal-open-meta">{{ $sealMeta }}</span>
                @endif
            </span>

            <span class="seal-tap-hint">{{ __('invitation.envelope_tap') }}</span>
            </span>
        </button>
    </div>

    <script>
        (function () {
            const stage = document.getElementById('seal-photo-stage');
            const trigger = document.getElementById('seal-photo-trigger');
            const particleLayer = document.getElementById('seal-photo-particles');

            if (!stage || !trigger) {
                return;
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const css = getComputedStyle(document.documentElement);
            const CROSSFADE = parseInt(css.getPropertyValue('--seal-crossfade'), 10) || 1350;
            const HOLD = parseInt(css.getPropertyValue('--seal-hold'), 10) || 900;
            const ZOOM = parseInt(css.getPropertyValue('--seal-zoom'), 10) || 900;
            const waxColor = css.getPropertyValue('--seal-wax').trim() || '#a83b57';
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

            function spawnParticle(index) {
                if (!particleLayer || particleLayer.childElementCount >= 24) {
                    return;
                }

                const particle = document.createElement('span');
                particle.className = 'seal-photo-particle';
                const isWax = index % 3 === 0;
                const size = isWax
                    ? (3 + Math.random() * 4).toFixed(1) + 'px'
                    : (2 + Math.random() * 4).toFixed(1) + 'px';

                particle.style.setProperty('--size', size);
                particle.style.setProperty('--height', size);
                particle.style.setProperty('--dx', (Math.random() * 220 - 110).toFixed(0) + 'px');
                particle.style.setProperty('--dy', (Math.random() * 180 - 120).toFixed(0) + 'px');
                particle.style.setProperty('--scale', (0.45 + Math.random() * 1).toFixed(2));
                particle.style.setProperty('--rot', (Math.random() * 140 - 70).toFixed(0) + 'deg');
                particle.style.setProperty('--duration', (0.8 + Math.random() * 0.8).toFixed(2) + 's');
                particle.style.setProperty('--color', isWax ? waxColor : (index % 4 === 1 ? '#fff7dc' : '#e4bc70'));
                particle.style.setProperty('--radius', isWax ? '18%' : '50%');

                particleLayer.appendChild(particle);
                particle.addEventListener('animationend', () => particle.remove(), { once: true });
            }

            function releaseParticles() {
                for (let index = 0; index < 22; index++) {
                    window.setTimeout(() => spawnParticle(index), index * 28);
                }
            }

            function beginOpening() {
                stage.classList.remove('seal-is-pressing');
                stage.classList.add('seal-is-opening');

                if (reduceMotion) {
                    window.setTimeout(showInviteContent, 180);
                    window.setTimeout(() => {
                        stage.classList.add('seal-is-fading');
                        finishReveal();
                    }, 420);
                    return;
                }

                window.setTimeout(releaseParticles, 160);

                window.setTimeout(() => {
                    stage.classList.add('seal-is-zooming');
                }, CROSSFADE + HOLD);

                window.setTimeout(showInviteContent, CROSSFADE + HOLD + 170);

                window.setTimeout(() => {
                    stage.classList.add('seal-is-fading');
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

                stage.classList.add('seal-is-pressing');

                window.setTimeout(beginOpening, reduceMotion ? 0 : 120);
            }

            trigger.addEventListener('click', reveal, { once: true });
        })();
    </script>
@endif

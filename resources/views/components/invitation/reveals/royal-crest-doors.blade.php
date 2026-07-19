@if (! $isPreview)
    @php
        $crestDate = $event->wedding_date->format('d · m · Y');
        $crestMeta = collect([$crestDate, $event->location_name])->filter()->implode(' — ');

        $crestAccents = [
            'amber-gold' => ['ink' => '#332516', 'gold' => '#b88b42', 'marble' => '#f8f2e8', 'sun' => '#fff4dc', 'wax' => '#c49a3a'],
            'royal-wedding' => ['ink' => '#172944', 'gold' => '#b18b32', 'marble' => '#f4f6fa', 'sun' => '#eef4ff', 'wax' => '#9a7a28'],
            'lavender-dream' => ['ink' => '#463653', 'gold' => '#9b7bb8', 'marble' => '#faf6fc', 'sun' => '#f4ecfa', 'wax' => '#8a6a98'],
            'winter-magic' => ['ink' => '#20364b', 'gold' => '#6299ba', 'marble' => '#f4f8fc', 'sun' => '#e8f4fc', 'wax' => '#4a7898'],
            'pearl-white' => ['ink' => '#33291f', 'gold' => '#8C7355', 'marble' => '#fdfaf6', 'sun' => '#faf6f0', 'wax' => '#7a6048'],
            'dusty-rose' => ['ink' => '#532f2c', 'gold' => '#B5706A', 'marble' => '#fdf6f4', 'sun' => '#fceee8', 'wax' => '#985860'],
            'paper-ink' => ['ink' => '#3A2E24', 'gold' => '#9A7B4F', 'marble' => '#faf8f4', 'sun' => '#f8f0e4', 'wax' => '#8a6b3f'],
        ];

        $crestAccent = $crestAccents[$event->theme->value] ?? $crestAccents['amber-gold'];
        $crestClosedUrl = asset('img/royal-crest-doors-reveal/nasdan-royal-crest-doors-closed.webp');
        $crestOpenUrl = asset('img/royal-crest-doors-reveal/nasdan-royal-crest-doors-open.webp');
    @endphp

    <style>
        :root {
            --crest-crossfade: 1350;
            --crest-hold: 900;
            --crest-zoom: 900;
            --crest-ink: {{ $crestAccent['ink'] }};
            --crest-gold: {{ $crestAccent['gold'] }};
            --crest-marble: {{ $crestAccent['marble'] }};
            --crest-sun: {{ $crestAccent['sun'] }};
            --crest-wax: {{ $crestAccent['wax'] }};
            --crest-wax-light: color-mix(in srgb, var(--crest-wax) 72%, white);
            --crest-wax-dark: color-mix(in srgb, var(--crest-wax) 78%, black);
        }

        .crest-photo-stage,
        .crest-photo-stage * {
            box-sizing: border-box;
        }

        .crest-photo-stage {
            --crest-closed-image: url('{{ $crestClosedUrl }}');
            position: fixed;
            inset: 0;
            z-index: 100;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: hidden;
            isolation: isolate;
            background: var(--crest-marble);
            opacity: 1;
            transition: opacity 0.78s ease, filter 0.78s ease;
        }

        .crest-photo-stage::before {
            content: '';
            position: absolute;
            z-index: -2;
            inset: -28px;
            background-image: var(--crest-closed-image);
            background-position: center;
            background-size: cover;
            filter: blur(22px) saturate(0.92) brightness(1.02);
            transform: scale(1.08);
        }

        .crest-photo-stage::after {
            content: none;
        }

        .crest-photo-stage.crest-is-opening::after {
            animation: none;
        }

        .crest-photo-stage.crest-is-fading {
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            pointer-events: none;
        }

        @keyframes crest-stage-bloom {
            0%, 100% { opacity: 0; }
            35% { opacity: 0.68; }
        }

        .crest-photo-trigger {
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

        .crest-photo-canvas {
            position: relative;
            width: min(577px, 100vw, calc(100dvh * 2 / 3));
            aspect-ratio: 2 / 3;
            max-height: calc(100dvh - env(safe-area-inset-top) - env(safe-area-inset-bottom));
            container-type: inline-size;
            container-name: crest-canvas;
            overflow: hidden;
            isolation: isolate;
            background: var(--crest-marble);
            box-shadow:
                0 24px 60px rgba(68, 48, 32, 0.14),
                0 0 0 1px rgba(184, 139, 66, 0.08);
        }

        .crest-photo-canvas::after {
            content: '';
            position: absolute;
            z-index: 8;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 46%, rgba(255, 244, 214, 0.58), transparent 36%);
            opacity: 0;
            transition: opacity 0.55s ease;
        }

        .crest-photo-stage.crest-is-opening .crest-photo-canvas::after {
            animation: crest-stage-bloom 1.25s ease-out both;
        }

        .crest-photo-trigger:focus-visible {
            outline: none;
        }

        .crest-photo-trigger:focus-visible .crest-photo-canvas {
            outline: 2px solid rgba(184, 139, 66, 0.95);
            outline-offset: -4px;
        }

        .crest-photo-trigger[aria-expanded='true'] {
            cursor: default;
            pointer-events: none;
        }

        .crest-photo-media {
            position: absolute;
            inset: 0;
            overflow: hidden;
            background: var(--crest-marble);
            transform: scale(1);
            transform-origin: 50% 46%;
            transition: transform 0.95s cubic-bezier(0.65, 0, 0.35, 1), filter 0.7s ease;
            will-change: transform, filter;
        }

        .crest-photo-media::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 4;
            pointer-events: none;
            background:
                linear-gradient(180deg, rgba(255, 252, 244, 0.12), transparent 24%),
                radial-gradient(ellipse at center, transparent 54%, rgba(184, 160, 120, 0.12) 100%);
        }

        .crest-photo-image {
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

        .crest-photo-open {
            z-index: 1;
            opacity: 0;
            filter: brightness(1.06) blur(2px);
            transform: scale(1.03);
            transition:
                opacity 1.15s cubic-bezier(0.4, 0, 0.2, 1) 0.18s,
                transform 1.55s cubic-bezier(0.16, 1, 0.3, 1) 0.12s,
                filter 1.05s ease 0.12s;
        }

        .crest-panels {
            position: absolute;
            inset: 0;
            z-index: 3;
            perspective: 1400px;
        }

        .crest-panel {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 50%;
            overflow: hidden;
            transform: translateX(0) rotateY(0deg);
            transition:
                transform 1.15s cubic-bezier(0.55, 0.02, 0.18, 1),
                opacity 0.85s ease 0.35s,
                filter 0.9s ease;
            will-change: transform, opacity, filter;
        }

        .crest-panel-left {
            left: 0;
            transform-origin: left center;
            box-shadow: inset -18px 0 36px rgba(0, 0, 0, 0.22);
        }

        .crest-panel-right {
            right: 0;
            transform-origin: right center;
            box-shadow: inset 18px 0 36px rgba(0, 0, 0, 0.22);
        }

        .crest-panel-image {
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

        .crest-panel-left .crest-panel-image {
            left: 0;
        }

        .crest-panel-right .crest-panel-image {
            left: -100%;
        }

        .crest-is-pressing .crest-panel-left,
        .crest-is-pressing .crest-panel-right {
            transform: scale(0.992);
            filter: brightness(0.98);
        }

        .crest-is-opening .crest-photo-open {
            opacity: 1;
            filter: brightness(1) blur(0);
            transform: scale(1);
        }

        .crest-is-opening .crest-panel-left {
            transform: translateX(-104%) rotateY(14deg) scaleX(0.94);
            opacity: 0.92;
            filter: brightness(0.96);
        }

        .crest-is-opening .crest-panel-right {
            transform: translateX(104%) rotateY(-14deg) scaleX(0.94);
            opacity: 0.92;
            filter: brightness(0.96);
        }

        .crest-is-zooming .crest-photo-media {
            filter: brightness(1.12);
            transform: scale(1.16);
        }

        .crest-wax-overlay {
            position: absolute;
            left: 50%;
            top: 46%;
            width: clamp(62px, 16cqw, 92px);
            height: clamp(62px, 16cqw, 92px);
            transform: translate(-50%, -50%);
            z-index: 6;
            pointer-events: none;
            filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.22));
            transition: transform 0.18s ease, opacity 0.35s ease 0.45s;
        }

        .crest-is-pressing .crest-wax-overlay {
            transform: translate(-50%, -50%) scale(0.92);
        }

        .crest-is-opening .crest-wax-overlay {
            opacity: 0;
        }

        .crest-wax-surface {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: radial-gradient(
                ellipse 70% 55% at 48% 22%,
                var(--crest-wax-light) 0%,
                var(--crest-wax) 65%,
                var(--crest-wax-dark) 100%
            );
            box-shadow:
                inset 0 1px 2px rgba(255, 255, 255, 0.32),
                inset 0 -3px 5px rgba(0, 0, 0, 0.18);
        }

        .crest-wax-base {
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

        .crest-wax-half {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            opacity: 0;
            transition:
                transform 0.55s cubic-bezier(0.4, 0.1, 0.3, 1) 0.14s,
                opacity 0.45s ease 0.14s;
        }

        .crest-wax-left {
            clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);
        }

        .crest-wax-right {
            clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%);
        }

        .crest-is-opening .crest-wax-base {
            opacity: 0;
        }

        .crest-is-opening .crest-wax-half {
            opacity: 1;
        }

        .crest-is-opening .crest-wax-left {
            transform: translateX(-18px) rotate(-8deg);
        }

        .crest-is-opening .crest-wax-right {
            transform: translateX(18px) rotate(8deg);
        }

        .crest-wax-emblem {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
        }

        .crest-wax-emblem path {
            fill: rgba(0, 0, 0, 0.22);
        }

        .crest-crack-line {
            position: absolute;
            inset: 0;
            z-index: 3;
            pointer-events: none;
            opacity: 0;
        }

        .crest-crack-line path {
            fill: none;
            stroke: rgba(255, 255, 255, 0.55);
            stroke-width: 1.4;
            stroke-linecap: round;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
        }

        .crest-is-pressing .crest-crack-line {
            opacity: 1;
        }

        .crest-is-pressing .crest-crack-line path {
            animation: crest-crack-draw 0.38s ease-out 0.08s forwards;
        }

        @keyframes crest-crack-draw {
            to { stroke-dashoffset: 0; }
        }

        .crest-crest-glint {
            position: absolute;
            z-index: 6;
            left: 50%;
            top: 46%;
            width: clamp(44px, 12cqw, 88px);
            height: clamp(44px, 12cqw, 88px);
            border-radius: 50%;
            opacity: 0;
            pointer-events: none;
            background: radial-gradient(
                circle at center,
                rgba(255, 248, 220, 0.92) 0%,
                rgba(255, 228, 160, 0.38) 42%,
                transparent 72%
            );
            transform: translate(-50%, -50%) scale(0.5);
        }

        .crest-is-pressing .crest-crest-glint {
            animation: crest-crest-glint 0.42s ease-out both;
        }

        @keyframes crest-crest-glint {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
            40% { opacity: 0.95; transform: translate(-50%, -50%) scale(1); }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.35); }
        }

        .crest-sun-bloom {
            position: absolute;
            z-index: 6;
            left: 50%;
            top: 46%;
            width: clamp(140px, 38cqw, 280px);
            height: clamp(140px, 38cqw, 280px);
            border-radius: 50%;
            opacity: 0;
            pointer-events: none;
            background: radial-gradient(
                ellipse at center,
                color-mix(in srgb, var(--crest-sun) 82%, white) 0%,
                rgba(255, 220, 160, 0.32) 38%,
                transparent 72%
            );
            transform: translate(-50%, -50%) scale(0.4);
        }

        .crest-is-opening .crest-sun-bloom {
            animation: crest-sun-bloom 1.15s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
        }

        @keyframes crest-sun-bloom {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.4); }
            28% { opacity: 0.95; }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.4); }
        }

        .crest-open-copy {
            position: absolute;
            z-index: 5;
            left: 50%;
            top: 31%;
            width: min(70cqw, 520px);
            color: var(--crest-ink);
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

        .crest-is-opening .crest-open-copy {
            opacity: 1;
            filter: blur(0);
            transform: translate(-50%, 0) scale(1);
        }

        .crest-is-zooming .crest-open-copy {
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, -12px) scale(1.12);
            transition:
                opacity 0.52s ease,
                transform 0.85s cubic-bezier(0.65, 0, 0.35, 1),
                filter 0.55s ease;
        }

        .crest-open-ornament {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(10px, 2.2cqw, 18px);
            margin-bottom: clamp(12px, 2.2cqh, 22px);
            color: var(--crest-gold);
        }

        .crest-open-ornament::before,
        .crest-open-ornament::after {
            content: '';
            width: clamp(34px, 10cqw, 84px);
            height: 1px;
            background: linear-gradient(90deg, transparent, currentColor);
        }

        .crest-open-ornament::after {
            background: linear-gradient(90deg, currentColor, transparent);
        }

        .crest-open-ornament svg {
            width: clamp(18px, 3.6cqw, 26px);
            height: auto;
        }

        .crest-open-eyebrow,
        .crest-open-names,
        .crest-open-meta {
            display: block;
        }

        .crest-open-eyebrow {
            margin-bottom: clamp(9px, 1.8cqh, 16px);
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.2cqw, 13px);
            font-weight: 500;
            line-height: 1.35;
            letter-spacing: clamp(3px, 0.8cqw, 6px);
            text-transform: uppercase;
            color: var(--crest-gold);
        }

        .crest-open-names {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(34px, 9cqw, 76px);
            font-weight: 600;
            font-style: italic;
            line-height: 1.03;
            text-wrap: balance;
        }

        .crest-open-rule {
            display: block;
            width: clamp(62px, 16cqw, 120px);
            height: 1px;
            margin: clamp(15px, 2.5cqh, 24px) auto clamp(11px, 2cqh, 18px);
            background: linear-gradient(90deg, transparent, var(--crest-gold), transparent);
        }

        .crest-open-meta {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(8px, 2cqw, 12px);
            font-weight: 500;
            line-height: 1.55;
            letter-spacing: clamp(1.3px, 0.45cqw, 3px);
            text-transform: uppercase;
            color: color-mix(in srgb, var(--crest-ink) 72%, white);
        }

        .crest-tap-hint {
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
            border: 1px solid rgba(184, 139, 66, 0.22);
            border-radius: 999px;
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.4cqw, 12px);
            font-weight: 500;
            line-height: 1;
            letter-spacing: clamp(2px, 0.9cqw, 6px);
            text-transform: uppercase;
            white-space: nowrap;
            color: rgba(68, 48, 32, 0.82);
            background: rgba(255, 248, 240, 0.78);
            box-shadow: 0 8px 28px rgba(184, 139, 66, 0.14);
            backdrop-filter: blur(8px);
            transform: translateX(-50%);
            transition: opacity 0.4s ease, transform 0.5s ease;
        }

        .crest-tap-hint::before {
            content: '';
            width: 6px;
            height: 6px;
            flex: 0 0 auto;
            border-radius: 50%;
            background: var(--crest-gold);
            animation: crest-tap-pulse 1.9s ease-out infinite;
        }

        .crest-is-pressing .crest-tap-hint,
        .crest-is-opening .crest-tap-hint {
            opacity: 0;
            transform: translate(-50%, 12px);
        }

        @keyframes crest-tap-pulse {
            0% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--crest-gold) 45%, transparent); }
            100% { box-shadow: 0 0 0 10px transparent; }
        }

        .crest-photo-particles {
            position: absolute;
            z-index: 7;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .crest-photo-particle {
            position: absolute;
            left: 50%;
            top: 46%;
            width: var(--size);
            height: var(--size);
            border-radius: 50%;
            background: #e4bc70;
            box-shadow: 0 0 10px rgba(228, 188, 112, 0.7);
            opacity: 0;
            animation: crest-photo-mote var(--duration) ease-out forwards;
        }

        @keyframes crest-photo-mote {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.2); }
            18% { opacity: 0.92; }
            100% {
                opacity: 0;
                transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(var(--scale));
            }
        }

        @container crest-canvas (max-width: 381px) {
            .crest-open-copy {
                width: 88%;
            }

            .crest-open-names {
                font-size: clamp(28px, 8.5cqw, 42px);
            }

            .crest-open-eyebrow {
                letter-spacing: clamp(2px, 0.7cqw, 4px);
            }

            .crest-tap-hint {
                letter-spacing: clamp(1.5px, 0.7cqw, 4px);
                padding: 10px 14px;
            }

            .crest-wax-overlay {
                width: clamp(54px, 15cqw, 78px);
                height: clamp(54px, 15cqw, 78px);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .crest-tap-hint::before,
            .crest-photo-particle,
            .crest-is-pressing .crest-crest-glint,
            .crest-is-pressing .crest-crack-line path,
            .crest-is-opening .crest-sun-bloom,
            .crest-photo-stage.crest-is-opening .crest-photo-canvas::after {
                animation: none;
            }

            .crest-photo-stage,
            .crest-photo-media,
            .crest-photo-image,
            .crest-panel,
            .crest-panel-image,
            .crest-open-copy,
            .crest-tap-hint,
            .crest-wax-half {
                transition-duration: 0.01ms !important;
                transition-delay: 0ms !important;
            }

            .crest-is-opening .crest-panel-left,
            .crest-is-opening .crest-panel-right {
                transform: none;
                opacity: 0;
            }

            .crest-is-opening .crest-wax-overlay {
                opacity: 0;
            }
        }
    </style>

    <div
        class="crest-photo-stage"
        id="crest-photo-stage"
        wire:ignore
    >
        <button
            type="button"
            class="crest-photo-trigger"
            id="crest-photo-trigger"
            aria-label="{{ __('invitation.envelope_open') }}"
            aria-expanded="false"
        >
            <span class="crest-photo-canvas">
            <span class="crest-photo-media" aria-hidden="true">
                <img
                    class="crest-photo-image crest-photo-open"
                    src="{{ $crestOpenUrl }}"
                    alt=""
                    loading="eager"
                    decoding="async"
                >

                <span class="crest-panels">
                    <span class="crest-panel crest-panel-left">
                        <img
                            class="crest-panel-image"
                            src="{{ $crestClosedUrl }}"
                            alt=""
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        >
                    </span>
                    <span class="crest-panel crest-panel-right">
                        <img
                            class="crest-panel-image"
                            src="{{ $crestClosedUrl }}"
                            alt=""
                            loading="eager"
                            decoding="async"
                        >
                    </span>
                </span>
            </span>

            <span class="crest-wax-overlay" aria-hidden="true">
                <span class="crest-wax-surface crest-wax-base"></span>
                <span class="crest-wax-surface crest-wax-half crest-wax-left"></span>
                <span class="crest-wax-surface crest-wax-half crest-wax-right"></span>
                <svg class="crest-wax-emblem" viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M24 8 L32 14 L30 24 L24 30 L18 24 L16 14 Z"/>
                </svg>
                <svg class="crest-crack-line" viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M24 12 L22 22 L26 28 L24 36"/>
                </svg>
            </span>

            <span class="crest-crest-glint" aria-hidden="true"></span>
            <span class="crest-sun-bloom" aria-hidden="true"></span>
            <span class="crest-photo-particles" id="crest-photo-particles" aria-hidden="true"></span>

            <span class="crest-open-copy">
                <span class="crest-open-ornament" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 3 L16 7 L14 12 L16 17 L12 21 L8 17 L10 12 L8 7 Z" stroke="currentColor" stroke-width="1.2" fill="currentColor" opacity="0.35"/>
                        <circle cx="12" cy="12" r="2.2" fill="currentColor"/>
                    </svg>
                </span>
                <span class="crest-open-eyebrow">{{ __('invitation.save_the_date') }}</span>
                <span class="crest-open-names">{{ $event->couple_names }}</span>
                <span class="crest-open-rule" aria-hidden="true"></span>
                @if ($crestMeta)
                    <span class="crest-open-meta">{{ $crestMeta }}</span>
                @endif
            </span>

            <span class="crest-tap-hint">{{ __('invitation.envelope_tap') }}</span>
            </span>
        </button>
    </div>

    <script>
        (function () {
            const stage = document.getElementById('crest-photo-stage');
            const trigger = document.getElementById('crest-photo-trigger');
            const particleLayer = document.getElementById('crest-photo-particles');

            if (!stage || !trigger) {
                return;
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const css = getComputedStyle(document.documentElement);
            const CROSSFADE = parseInt(css.getPropertyValue('--crest-crossfade'), 10) || 1350;
            const HOLD = parseInt(css.getPropertyValue('--crest-hold'), 10) || 900;
            const ZOOM = parseInt(css.getPropertyValue('--crest-zoom'), 10) || 900;
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

            function spawnMote(index) {
                if (!particleLayer || particleLayer.childElementCount >= 22) {
                    return;
                }

                const particle = document.createElement('span');
                particle.className = 'crest-photo-particle';
                particle.style.setProperty('--size', (2 + Math.random() * 5).toFixed(1) + 'px');
                particle.style.setProperty('--dx', (Math.random() * 220 - 110).toFixed(0) + 'px');
                particle.style.setProperty('--dy', (Math.random() * -180 - 40).toFixed(0) + 'px');
                particle.style.setProperty('--scale', (0.5 + Math.random() * 1.1).toFixed(2));
                particle.style.setProperty('--duration', (0.85 + Math.random() * 0.85).toFixed(2) + 's');

                const palette = ['#e4bc70', '#fff7dc', '#f0d890', '#ffffff'];
                particle.style.background = palette[index % palette.length];
                particle.style.boxShadow = '0 0 10px color-mix(in srgb, ' + palette[index % palette.length] + ' 70%, transparent)';

                particleLayer.appendChild(particle);
                particle.addEventListener('animationend', () => particle.remove(), { once: true });
            }

            function releaseMotes() {
                for (let index = 0; index < 20; index++) {
                    window.setTimeout(() => spawnMote(index), index * 30);
                }
            }

            function beginOpening() {
                stage.classList.remove('crest-is-pressing');
                stage.classList.add('crest-is-opening');

                if (reduceMotion) {
                    window.setTimeout(showInviteContent, 180);
                    window.setTimeout(() => {
                        stage.classList.add('crest-is-fading');
                        finishReveal();
                    }, 420);
                    return;
                }

                window.setTimeout(releaseMotes, 160);

                window.setTimeout(() => {
                    stage.classList.add('crest-is-zooming');
                }, CROSSFADE + HOLD);

                window.setTimeout(showInviteContent, CROSSFADE + HOLD + 170);

                window.setTimeout(() => {
                    stage.classList.add('crest-is-fading');
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

                stage.classList.add('crest-is-pressing');

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

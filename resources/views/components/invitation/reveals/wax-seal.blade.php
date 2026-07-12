@if (! $isPreview)
    @php
        $sealDate = $event->wedding_date->format('d · m · Y');
        $sealMeta = collect([$sealDate, $event->location_name])->filter()->implode(' — ');

        $sealPalettes = [
            'amber-gold' => [
                '--seal-navy-1' => '#1a1208',
                '--seal-navy-2' => '#2a1f0f',
                '--seal-flap-2' => '#3d2910',
                '--seal-liner' => '#f5e6c8',
                '--seal-paper' => '#fbf6ee',
                '--seal-ink' => '#1a1208',
                '--seal-gold' => '#c9a227',
                '--seal-bg-1' => '#0d0a04',
                '--seal-bg-2' => '#1a1208',
            ],
            'royal-wedding' => [
                '--seal-navy-1' => '#0f1a2e',
                '--seal-navy-2' => '#1a2744',
                '--seal-flap-2' => '#1e3a5f',
                '--seal-liner' => '#d4e0f0',
                '--seal-paper' => '#f8f6f0',
                '--seal-ink' => '#0f1a2e',
                '--seal-gold' => '#d4af37',
                '--seal-bg-1' => '#080f1a',
                '--seal-bg-2' => '#0f1a2e',
            ],
            'lavender-dream' => [
                '--seal-navy-1' => '#2d2438',
                '--seal-navy-2' => '#3d3249',
                '--seal-flap-2' => '#9b7bb8',
                '--seal-liner' => '#e8dff5',
                '--seal-paper' => '#faf8fc',
                '--seal-ink' => '#2d2438',
                '--seal-gold' => '#c9b8e0',
                '--seal-bg-1' => '#1a1220',
                '--seal-bg-2' => '#2d2438',
            ],
            'winter-magic' => [
                '--seal-navy-1' => '#1a2332',
                '--seal-navy-2' => '#243044',
                '--seal-flap-2' => '#7eb8da',
                '--seal-liner' => '#e8f4fc',
                '--seal-paper' => '#f0f8ff',
                '--seal-ink' => '#1a2332',
                '--seal-gold' => '#7eb8da',
                '--seal-bg-1' => '#0d1520',
                '--seal-bg-2' => '#1a2332',
            ],
            'pearl-white' => [
                '--seal-navy-1' => '#8C7355',
                '--seal-navy-2' => '#6E5A42',
                '--seal-flap-2' => '#a88b6a',
                '--seal-liner' => '#E8E0D5',
                '--seal-paper' => '#FAFAF8',
                '--seal-ink' => '#1C1917',
                '--seal-gold' => '#8C7355',
                '--seal-bg-1' => '#5a4535',
                '--seal-bg-2' => '#6E5A42',
            ],
            'dusty-rose' => [
                '--seal-navy-1' => '#B5706A',
                '--seal-navy-2' => '#9A5A55',
                '--seal-flap-2' => '#c98580',
                '--seal-liner' => '#E8C9C4',
                '--seal-paper' => '#F9F1EE',
                '--seal-ink' => '#2D1B16',
                '--seal-gold' => '#B5706A',
                '--seal-bg-1' => '#7a3a35',
                '--seal-bg-2' => '#9A5A55',
            ],
            'paper-ink' => [
                '--seal-navy-1' => '#9A7B4F',
                '--seal-navy-2' => '#7A623E',
                '--seal-flap-2' => '#b8956a',
                '--seal-liner' => '#C4B59A',
                '--seal-paper' => '#F3EDE3',
                '--seal-ink' => '#3A2E24',
                '--seal-gold' => '#9A7B4F',
                '--seal-bg-1' => '#5a4a32',
                '--seal-bg-2' => '#7A623E',
            ],
        ];

        $sealPalette = $sealPalettes[$event->theme->value] ?? $sealPalettes['amber-gold'];

        $sealSunburstPoints = collect(range(0, 35))->map(function (int $i) {
            $angle = deg2rad($i * 10 - 90);
            $radius = $i % 2 === 0 ? 34 : 27;

            return [
                'x' => round(38 + $radius * cos($angle), 1),
                'y' => round(38 + $radius * sin($angle), 1),
            ];
        });

        $sealSunburst = $sealSunburstPoints
            ->map(fn (array $point) => "{$point['x']}px {$point['y']}px")
            ->implode(', ');

        $sealCrackPoints = collect(range(0, 7))->map(function (int $i) {
            $y = 6 + ($i * 9);
            $x = 38 + ($i % 2 === 0 ? 5 : -5);

            return [
                'x' => round($x, 1),
                'y' => round($y, 1),
            ];
        });

        $sealLeftArc = $sealSunburstPoints
            ->filter(fn (array $point) => $point['x'] <= 38.05)
            ->sortBy(fn (array $point) => atan2($point['y'] - 38, $point['x'] - 38))
            ->values();

        $sealRightArc = $sealSunburstPoints
            ->filter(fn (array $point) => $point['x'] >= 37.95)
            ->sortByDesc(fn (array $point) => atan2($point['y'] - 38, $point['x'] - 38))
            ->values();

        $formatPoints = fn ($points) => collect($points)
            ->map(fn (array $point) => "{$point['x']}px {$point['y']}px")
            ->implode(', ');

        $sealHalfLeftClip = 'polygon('.$formatPoints(
            $sealCrackPoints->merge($sealLeftArc->reverse())
        ).')';

        $sealHalfRightClip = 'polygon('.$formatPoints(
            $sealCrackPoints->reverse()->merge($sealRightArc)
        ).')';
    @endphp

    <style>
        :root {
            --seal-open: 1400;
            --seal-hold: 1000;
            --seal-zoom: 600;
            @foreach ($sealPalette as $var => $val)
                {{ $var }}: {{ $val }};
            @endforeach
            --seal-wax-1: color-mix(in srgb, var(--seal-gold), #ffffff 45%);
            --seal-wax-2: var(--seal-gold);
            --seal-wax-3: color-mix(in srgb, var(--seal-gold), #000000 42%);
        }

        .seal-stage {
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
                ellipse 85% 70% at 50% 45%,
                color-mix(in srgb, var(--seal-bg-2) 88%, black) 0%,
                var(--seal-bg-1) 52%,
                #050403 100%
            );
        }

        .seal-stage.seal-gone {
            opacity: 0;
            pointer-events: none;
        }

        .seal-card {
            position: relative;
            width: 300px;
            height: 380px;
            cursor: pointer;
            transform-style: preserve-3d;
            animation: seal-bob 6s ease-in-out infinite;
        }

        .seal-card.seal-breaking {
            animation: none;
            pointer-events: none;
        }

        @keyframes seal-bob {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-9px); }
        }

        .seal-card::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -34px;
            width: 70%;
            height: 26px;
            background: rgba(0, 0, 0, 0.4);
            filter: blur(16px);
            border-radius: 50%;
            transform: translateX(-50%);
            transition: 0.6s ease;
        }

        .seal-card.seal-breaking::after {
            width: 56%;
            opacity: 0.6;
        }

        .seal-inner {
            position: absolute;
            inset: 0;
            border-radius: 16px;
            overflow: hidden;
            background: linear-gradient(180deg, #fffdf8, var(--seal-paper));
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            transform: scale(0.97);
            transition: transform 0.7s cubic-bezier(0.2, 0.9, 0.25, 1) 0.4s,
                opacity 0.5s ease,
                filter 0.5s ease;
        }

        .seal-inner::before {
            content: "";
            position: absolute;
            inset: 12px;
            border: 1px solid color-mix(in srgb, var(--seal-gold) 55%, transparent);
            border-radius: 9px;
            pointer-events: none;
        }

        .seal-inner-body {
            color: var(--seal-ink);
            line-height: 1;
            padding: 0 22px;
        }

        .seal-eyebrow {
            display: block;
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--seal-gold);
            margin-bottom: 12px;
        }

        .seal-names {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 600;
            font-style: italic;
            color: var(--seal-ink);
        }

        .seal-rule {
            width: 44px;
            height: 1px;
            background: var(--seal-gold);
            margin: 14px auto 10px;
            position: relative;
        }

        .seal-rule::after {
            content: "♥";
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -52%);
            background: var(--seal-paper);
            color: var(--seal-gold);
            font-size: 11px;
            padding: 0 6px;
        }

        .seal-meta {
            font-size: 11px;
            letter-spacing: 2px;
            color: #8a8ca0;
            text-transform: uppercase;
        }

        .seal-door {
            position: absolute;
            top: 0;
            height: 100%;
            width: 50%;
            z-index: 4;
            overflow: hidden;
            background:
                repeating-linear-gradient(
                    45deg,
                    transparent 0,
                    transparent 7px,
                    color-mix(in srgb, var(--seal-gold) 8%, transparent) 7px,
                    color-mix(in srgb, var(--seal-gold) 8%, transparent) 8px
                ),
                linear-gradient(155deg, var(--seal-navy-1) 0%, var(--seal-navy-2) 58%, color-mix(in srgb, var(--seal-navy-1) 70%, black) 100%);
            box-shadow: inset 0 -14px 34px rgba(0, 0, 0, 0.22),
                inset 0 1px 0 color-mix(in srgb, var(--seal-gold) 18%, transparent);
            transition: transform 0.75s cubic-bezier(0.5, 0.05, 0.2, 1),
                opacity 0.6s ease;
        }

        .seal-door.seal-left {
            left: 0;
            transform-origin: left center;
            border-radius: 16px 2px 2px 16px;
        }

        .seal-door.seal-right {
            right: 0;
            transform-origin: right center;
            border-radius: 2px 16px 16px 2px;
        }

        .seal-door::before {
            content: "";
            position: absolute;
            top: 16px;
            bottom: 16px;
            width: 1px;
            background: color-mix(in srgb, var(--seal-gold) 70%, transparent);
        }

        .seal-door.seal-left::before { right: 0; }
        .seal-door.seal-right::before { left: 0; }

        .seal-door::after {
            content: "";
            position: absolute;
            inset: 14px;
            border: 1px solid color-mix(in srgb, var(--seal-gold) 40%, transparent);
            border-radius: 8px;
            box-shadow:
                inset 0 0 0 1px color-mix(in srgb, var(--seal-gold) 12%, transparent),
                0 0 0 1px color-mix(in srgb, var(--seal-gold) 8%, transparent);
        }

        .seal-door-ornament {
            position: absolute;
            width: 34px;
            height: 34px;
            color: color-mix(in srgb, var(--seal-gold) 35%, transparent);
            pointer-events: none;
            z-index: 1;
        }

        .seal-door.seal-left .seal-door-ornament {
            top: 18px;
            left: 18px;
        }

        .seal-door.seal-right .seal-door-ornament {
            top: 18px;
            right: 18px;
            transform: scaleX(-1);
        }

        .seal-door.seal-left .seal-door-ornament.seal-ornament-bl {
            top: auto;
            left: 18px;
            bottom: 18px;
            transform: scaleY(-1);
        }

        .seal-door.seal-right .seal-door-ornament.seal-ornament-br {
            top: auto;
            right: 18px;
            bottom: 18px;
            transform: scale(-1, -1);
        }

        .seal-ribbon {
            position: absolute;
            z-index: 5;
            pointer-events: none;
            background: linear-gradient(
                180deg,
                color-mix(in srgb, var(--seal-gold) 72%, white),
                color-mix(in srgb, var(--seal-gold) 55%, transparent) 45%,
                color-mix(in srgb, var(--seal-gold) 38%, black)
            );
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
            transition: opacity 0.45s ease 0.18s, transform 0.5s ease 0.18s;
        }

        .seal-ribbon-h {
            left: 0;
            right: 0;
            top: 50%;
            height: 3px;
            transform: translateY(-50%);
        }

        .seal-ribbon-v {
            top: 0;
            bottom: 0;
            left: 50%;
            width: 3px;
            transform: translateX(-50%);
        }

        .seal-card.seal-breaking .seal-ribbon {
            opacity: 0;
            transform: scale(0.92);
        }

        .seal-card.seal-breaking .seal-ribbon-h {
            transform: translateY(-50%) scaleX(0.85);
        }

        .seal-card.seal-breaking .seal-ribbon-v {
            transform: translateX(-50%) scaleY(0.85);
        }

        /* flip the doors open — flip the sign of rotateY if they open inward */
        .seal-card.seal-breaking .seal-door.seal-left {
            transform: rotateY(-125deg) translateZ(30px);
            opacity: 0;
            transition-delay: 0.4s;
        }

        .seal-card.seal-breaking .seal-door.seal-right {
            transform: rotateY(125deg) translateZ(30px);
            opacity: 0;
            transition-delay: 0.46s;
        }

        .seal-card.seal-breaking .seal-inner {
            transform: scale(1);
        }

        .seal-wax {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 76px;
            height: 76px;
            transform: translate(-50%, -50%);
            z-index: 6;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.35));
        }

        .seal-wax-surface {
            background: radial-gradient(
                ellipse 70% 55% at 48% 22%,
                var(--seal-wax-1) 0%,
                var(--seal-wax-2) 65%,
                var(--seal-wax-3) 100%
            );
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.22),
                inset 0 -3px 5px rgba(0, 0, 0, 0.28);
        }

        .seal-wax-base {
            position: absolute;
            inset: 0;
            clip-path: polygon({{ $sealSunburst }});
            transition: opacity 0.2s ease;
        }

        .seal-wax-base::after {
            content: "";
            position: absolute;
            inset: 9px;
            border-radius: 50%;
            border: 1px solid color-mix(in srgb, var(--seal-wax-3) 55%, transparent);
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--seal-wax-1) 35%, transparent);
            pointer-events: none;
        }

        .seal-half {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: transform 0.55s cubic-bezier(0.4, 0.1, 0.3, 1) 0.12s,
                opacity 0.5s ease 0.12s;
        }

        .seal-half.seal-left {
            clip-path: {{ $sealHalfLeftClip }};
        }

        .seal-half.seal-right {
            clip-path: {{ $sealHalfRightClip }};
        }

        .seal-card.seal-breaking .seal-wax-base {
            opacity: 0;
        }

        .seal-card.seal-breaking .seal-half {
            opacity: 1;
        }

        .seal-card.seal-breaking .seal-emblem {
            opacity: 0;
            transition: opacity 0.25s ease 0.1s;
        }

        .seal-emblem {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
        }

        .seal-emblem path {
            fill: rgba(0, 0, 0, 0.28);
            filter: drop-shadow(0 1px 0 rgba(255, 255, 255, 0.12));
        }

        .seal-crack {
            position: absolute;
            inset: 0;
            z-index: 3;
            opacity: 0;
            pointer-events: none;
        }

        .seal-crack path {
            stroke: rgba(255, 248, 220, 0.95);
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            filter: drop-shadow(0 0 3px rgba(255, 240, 190, 0.8));
        }

        .seal-card.seal-breaking .seal-crack {
            animation: seal-crack 0.45s ease forwards;
        }

        @keyframes seal-crack {
            0% { opacity: 0; }
            25% { opacity: 1; }
            100% { opacity: 0; }
        }

        .seal-card.seal-breaking .seal-half.seal-left {
            transform: translate(-18px, 14px) rotate(-16deg);
            opacity: 0;
        }

        .seal-card.seal-breaking .seal-half.seal-right {
            transform: translate(18px, 14px) rotate(16deg);
            opacity: 0;
        }

        .seal-card.seal-zoom .seal-inner {
            transform: scale(4.8) translateY(-6px);
            opacity: 0;
            filter: blur(6px);
            transition: transform 0.6s cubic-bezier(0.5, 0, 0.85, 0.35),
                opacity 0.5s ease-in 0.1s,
                filter 0.55s ease-in;
        }

        .seal-hearts {
            position: absolute;
            inset: 0;
            z-index: 8;
            pointer-events: none;
            overflow: visible;
        }

        .seal-hearts .seal-h {
            position: absolute;
            will-change: transform, opacity;
            animation: seal-rise var(--dur, 2.4s) ease-out forwards;
        }

        @keyframes seal-rise {
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

        .seal-hint {
            position: absolute;
            left: 50%;
            bottom: -64px;
            transform: translateX(-50%);
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            transition: opacity 0.3s ease;
            white-space: nowrap;
        }

        .seal-card.seal-breaking .seal-hint {
            opacity: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            .seal-card { animation: none; }
            .seal-door,
            .seal-half,
            .seal-ribbon,
            .seal-wax-base,
            .seal-inner { transition-duration: 0.001s; }
        }
    </style>

    <div class="seal-stage" id="seal-stage" wire:ignore>
        <div
            class="seal-card"
            id="seal-card"
            role="button"
            tabindex="0"
            aria-label="{{ __('invitation.envelope_open') }}"
        >
            <div class="seal-inner">
                <div class="seal-inner-body">
                    <span class="seal-eyebrow">{{ __('invitation.save_the_date') }}</span>
                    <div class="seal-names">{{ $event->couple_names }}</div>
                    <div class="seal-rule"></div>
                    @if ($sealMeta)
                        <div class="seal-meta">{{ $sealMeta }}</div>
                    @endif
                </div>
            </div>

            <div class="seal-door seal-left">
                <svg class="seal-door-ornament" viewBox="0 0 34 34" aria-hidden="true">
                    <path fill="currentColor" d="M17 2 C14 6 10 8 6 10 C10 12 12 16 12 20 C14 17 17 16 20 17 C17 14 16 10 17 6 C18 10 17 14 20 17 C22 16 24 17 26 20 C26 16 28 12 32 10 C28 8 24 6 21 2 C20 6 18 8 17 10 C16 8 14 6 17 2 Z"/>
                </svg>
                <svg class="seal-door-ornament seal-ornament-bl" viewBox="0 0 34 34" aria-hidden="true">
                    <path fill="currentColor" d="M17 2 C14 6 10 8 6 10 C10 12 12 16 12 20 C14 17 17 16 20 17 C17 14 16 10 17 6 C18 10 17 14 20 17 C22 16 24 17 26 20 C26 16 28 12 32 10 C28 8 24 6 21 2 C20 6 18 8 17 10 C16 8 14 6 17 2 Z"/>
                </svg>
            </div>
            <div class="seal-door seal-right">
                <svg class="seal-door-ornament" viewBox="0 0 34 34" aria-hidden="true">
                    <path fill="currentColor" d="M17 2 C14 6 10 8 6 10 C10 12 12 16 12 20 C14 17 17 16 20 17 C17 14 16 10 17 6 C18 10 17 14 20 17 C22 16 24 17 26 20 C26 16 28 12 32 10 C28 8 24 6 21 2 C20 6 18 8 17 10 C16 8 14 6 17 2 Z"/>
                </svg>
                <svg class="seal-door-ornament seal-ornament-br" viewBox="0 0 34 34" aria-hidden="true">
                    <path fill="currentColor" d="M17 2 C14 6 10 8 6 10 C10 12 12 16 12 20 C14 17 17 16 20 17 C17 14 16 10 17 6 C18 10 17 14 20 17 C22 16 24 17 26 20 C26 16 28 12 32 10 C28 8 24 6 21 2 C20 6 18 8 17 10 C16 8 14 6 17 2 Z"/>
                </svg>
            </div>

            <div class="seal-ribbon seal-ribbon-h" aria-hidden="true"></div>
            <div class="seal-ribbon seal-ribbon-v" aria-hidden="true"></div>

            <div class="seal-wax" aria-hidden="true">
                <div class="seal-wax-base seal-wax-surface"></div>
                <div class="seal-half seal-left seal-wax-surface"></div>
                <div class="seal-half seal-right seal-wax-surface"></div>
                <svg class="seal-emblem" viewBox="0 0 76 76">
                    <path d="M38 52 C29 45 21 40 21 32 C21 26 28 23 32 28 C34 31 38 31 41 28 C45 23 52 26 52 32 C52 40 44 45 38 52 Z"/>
                </svg>
                <svg class="seal-crack" viewBox="0 0 76 76"><path d="M38 6 L43 15 L33 24 L43 33 L33 42 L43 51 L34 60 L40 70"/></svg>
            </div>

            <div class="seal-hearts" id="seal-hearts" aria-hidden="true"></div>
            <span class="seal-hint text-center">{{ __('invitation.envelope_tap') }}</span>
        </div>
    </div>

    <script>
        (function () {
            const card = document.getElementById('seal-card');
            const stage = document.getElementById('seal-stage');
            const layer = document.getElementById('seal-hearts');
            const colors = ['#e2506a', '#ff86a0', '#ffc9d4', '#c9a24b', '#ffffff'];
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const css = getComputedStyle(document.documentElement);
            const OPEN = parseInt(css.getPropertyValue('--seal-open'), 10) || 1400;
            const HOLD = parseInt(css.getPropertyValue('--seal-hold'), 10) || 1000;
            const ZOOM = parseInt(css.getPropertyValue('--seal-zoom'), 10) || 600;

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
                el.className = 'seal-h';
                const size = 10 + Math.random() * 14;
                el.style.left = (30 + Math.random() * 40) + '%';
                el.style.top = (40 + Math.random() * 18) + '%';
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
                    stage.classList.add('seal-gone');
                    finishReveal();
                    return;
                }

                card.classList.add('seal-breaking');
                heartTimer = setInterval(spawnHeart, 170);

                setTimeout(() => {
                    clearInterval(heartTimer);
                    card.classList.add('seal-zoom');
                }, OPEN + HOLD);

                setTimeout(() => {
                    showInviteContent();
                }, OPEN + HOLD + 150);

                setTimeout(() => {
                    stage.classList.add('seal-gone');
                    finishReveal();
                }, OPEN + HOLD + ZOOM);
            }

            card.addEventListener('click', reveal);
            card.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    reveal();
                }
            });
        })();
    </script>
@endif
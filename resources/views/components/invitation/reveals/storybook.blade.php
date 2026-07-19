@if (! $isPreview)
    @php
        $storyDate = $event->wedding_date->format('d · m · Y');
        $storyMeta = collect([$storyDate, $event->location_name])->filter()->implode(' — ');

        $storyAccents = [
            'amber-gold' => ['ink' => '#332516', 'gold' => '#b88b42'],
            'royal-wedding' => ['ink' => '#172944', 'gold' => '#b18b32'],
            'lavender-dream' => ['ink' => '#463653', 'gold' => '#9b7bb8'],
            'winter-magic' => ['ink' => '#20364b', 'gold' => '#6299ba'],
            'pearl-white' => ['ink' => '#33291f', 'gold' => '#8C7355'],
            'dusty-rose' => ['ink' => '#532f2c', 'gold' => '#B5706A'],
            'paper-ink' => ['ink' => '#3A2E24', 'gold' => '#9A7B4F'],
        ];

        $storyAccent = $storyAccents[$event->theme->value] ?? $storyAccents['amber-gold'];
        $storyClosedUrl = asset('img/storybook-reveal/nasdan-storybook-closed.webp');
        $storyOpenUrl = asset('img/storybook-reveal/nasdan-storybook-open.webp');
    @endphp

    <style>
        :root {
            --story-crossfade: 1350;
            --story-hold: 900;
            --story-zoom: 900;
            --story-ink: {{ $storyAccent['ink'] }};
            --story-gold: {{ $storyAccent['gold'] }};
        }

        .story-photo-stage,
        .story-photo-stage * {
            box-sizing: border-box;
        }

        .story-photo-stage {
            --story-closed-image: url('{{ $storyClosedUrl }}');
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

        .story-photo-stage::before {
            content: '';
            position: absolute;
            z-index: -2;
            inset: -28px;
            background-image: var(--story-closed-image);
            background-position: center;
            background-size: cover;
            filter: blur(22px) saturate(0.72) brightness(0.88);
            transform: scale(1.08);
        }

        .story-photo-stage::after {
            content: '';
            position: absolute;
            z-index: 8;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 44%, rgba(255, 248, 228, 0.56), transparent 34%);
            opacity: 0;
            transition: opacity 0.55s ease;
        }

        .story-photo-stage.story-is-opening::after {
            animation: story-stage-bloom 1.2s ease-out both;
        }

        .story-photo-stage.story-is-fading {
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            pointer-events: none;
        }

        @keyframes story-stage-bloom {
            0%, 100% { opacity: 0; }
            36% { opacity: 0.58; }
        }

        .story-photo-trigger {
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

        .story-photo-trigger:focus-visible {
            outline: 2px solid rgba(184, 139, 66, 0.95);
            outline-offset: -8px;
        }

        .story-photo-trigger[aria-expanded='true'] {
            cursor: default;
            pointer-events: none;
        }

        .story-photo-media {
            position: absolute;
            inset: 0;
            overflow: hidden;
            background: #f1e9dd;
            transform: scale(1);
            transform-origin: 50% 44%;
            transition: transform 0.95s cubic-bezier(0.65, 0, 0.35, 1), filter 0.7s ease;
            will-change: transform, filter;
            perspective: 1600px;
        }

        .story-photo-media::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 4;
            pointer-events: none;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.07), transparent 24%),
                radial-gradient(ellipse at center, transparent 54%, rgba(40, 24, 16, 0.14) 100%);
        }

        .story-photo-image {
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

        .story-photo-open {
            z-index: 1;
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            transform: scale(1.035);
            transition:
                opacity 1.2s cubic-bezier(0.4, 0, 0.2, 1) 0.16s,
                transform 1.65s cubic-bezier(0.16, 1, 0.3, 1) 0.12s,
                filter 1.1s ease 0.12s;
        }

        .story-cover-panel {
            position: absolute;
            inset: 0;
            z-index: 3;
            overflow: hidden;
            transform: rotateY(0deg);
            transform-origin: left center;
            transition:
                transform 1.15s cubic-bezier(0.55, 0.02, 0.18, 1),
                opacity 0.85s ease 0.4s,
                filter 0.9s ease;
            will-change: transform, opacity, filter;
            box-shadow: inset -18px 0 36px rgba(0, 0, 0, 0.28);
        }

        .story-cover-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            user-select: none;
            -webkit-user-drag: none;
        }

        .story-is-pressing .story-photo-media {
            transform: scale(0.985);
        }

        .story-is-opening .story-photo-open {
            opacity: 1;
            filter: brightness(1) blur(0);
            transform: scale(1);
        }

        .story-is-opening .story-cover-panel {
            transform: rotateY(-92deg) translateX(-2%);
            opacity: 0.88;
            filter: brightness(0.94);
        }

        .story-is-zooming .story-photo-media {
            filter: brightness(1.09);
            transform: scale(1.16);
        }

        .story-edge-glint {
            position: absolute;
            z-index: 6;
            left: 50%;
            top: 44%;
            width: clamp(4px, 1vw, 8px);
            height: clamp(120px, 32vh, 260px);
            border-radius: 999px;
            opacity: 0;
            pointer-events: none;
            background: linear-gradient(
                180deg,
                transparent,
                rgba(255, 236, 170, 0.92) 42%,
                rgba(255, 236, 170, 0.92) 58%,
                transparent
            );
            box-shadow: 0 0 18px rgba(255, 228, 150, 0.55);
            transform: translate(-50%, -50%) scaleY(0.4);
        }

        .story-is-opening .story-edge-glint {
            animation: story-edge-glint 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.06s both;
        }

        @keyframes story-edge-glint {
            0% { opacity: 0; transform: translate(-50%, -50%) scaleY(0.4); }
            28% { opacity: 0.95; }
            100% { opacity: 0; transform: translate(-50%, -50%) scaleY(1.15); }
        }

        .story-gutter-light {
            position: absolute;
            z-index: 6;
            left: 50%;
            top: 44%;
            width: clamp(90px, 24vw, 180px);
            height: clamp(140px, 36vh, 300px);
            border-radius: 50%;
            opacity: 0;
            pointer-events: none;
            background: radial-gradient(
                ellipse at center,
                rgba(255, 248, 220, 0.7) 0%,
                rgba(255, 228, 160, 0.26) 40%,
                transparent 72%
            );
            transform: translate(-50%, -50%) scale(0.35);
        }

        .story-is-opening .story-gutter-light {
            animation: story-gutter-bloom 1.05s cubic-bezier(0.16, 1, 0.3, 1) 0.18s both;
        }

        @keyframes story-gutter-bloom {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.35); }
            30% { opacity: 0.92; }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.4); }
        }

        .story-open-copy {
            position: absolute;
            z-index: 5;
            left: 50%;
            top: 30%;
            width: min(70vw, 520px);
            color: var(--story-ink);
            text-align: center;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.65);
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, 28px) scale(0.97);
            transition:
                opacity 0.9s ease 0.76s,
                transform 1.05s cubic-bezier(0.16, 1, 0.3, 1) 0.74s,
                filter 0.8s ease 0.74s;
            pointer-events: none;
        }

        .story-is-opening .story-open-copy {
            opacity: 1;
            filter: blur(0);
            transform: translate(-50%, 0) scale(1);
        }

        .story-is-zooming .story-open-copy {
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, -12px) scale(1.12);
            transition:
                opacity 0.52s ease,
                transform 0.85s cubic-bezier(0.65, 0, 0.35, 1),
                filter 0.55s ease;
        }

        .story-open-ornament {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(10px, 2.2vw, 18px);
            margin-bottom: clamp(12px, 2.2vh, 22px);
            color: var(--story-gold);
        }

        .story-open-ornament::before,
        .story-open-ornament::after {
            content: '';
            width: clamp(34px, 10vw, 84px);
            height: 1px;
            background: linear-gradient(90deg, transparent, currentColor);
        }

        .story-open-ornament::after {
            background: linear-gradient(90deg, currentColor, transparent);
        }

        .story-open-ornament svg {
            width: clamp(15px, 3.2vw, 23px);
            height: auto;
        }

        .story-open-eyebrow,
        .story-open-names,
        .story-open-meta {
            display: block;
        }

        .story-open-eyebrow {
            margin-bottom: clamp(9px, 1.8vh, 16px);
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.2vw, 13px);
            font-weight: 500;
            line-height: 1.35;
            letter-spacing: clamp(3px, 0.8vw, 6px);
            text-transform: uppercase;
            color: var(--story-gold);
        }

        .story-open-names {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(34px, 9vw, 76px);
            font-weight: 600;
            font-style: italic;
            line-height: 1.03;
            text-wrap: balance;
        }

        .story-open-rule {
            display: block;
            width: clamp(62px, 16vw, 120px);
            height: 1px;
            margin: clamp(15px, 2.5vh, 24px) auto clamp(11px, 2vh, 18px);
            background: linear-gradient(90deg, transparent, var(--story-gold), transparent);
        }

        .story-open-meta {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(8px, 2vw, 12px);
            font-weight: 500;
            line-height: 1.55;
            letter-spacing: clamp(1.3px, 0.45vw, 3px);
            text-transform: uppercase;
            color: color-mix(in srgb, var(--story-ink) 72%, white);
        }

        .story-tap-hint {
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
            color: rgba(74, 51, 29, 0.77);
            background: rgba(255, 251, 244, 0.46);
            box-shadow: 0 8px 28px rgba(80, 55, 30, 0.08);
            backdrop-filter: blur(8px);
            transform: translateX(-50%);
            transition: opacity 0.4s ease, transform 0.5s ease;
        }

        .story-tap-hint::before {
            content: '';
            width: 6px;
            height: 6px;
            flex: 0 0 auto;
            border-radius: 50%;
            background: var(--story-gold);
            animation: story-tap-pulse 1.9s ease-out infinite;
        }

        .story-is-opening .story-tap-hint,
        .story-is-pressing .story-tap-hint {
            opacity: 0;
            transform: translate(-50%, 12px);
        }

        @keyframes story-tap-pulse {
            0% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--story-gold) 45%, transparent); }
            100% { box-shadow: 0 0 0 10px transparent; }
        }

        .story-photo-particles {
            position: absolute;
            z-index: 7;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .story-photo-particle {
            position: absolute;
            left: 50%;
            top: 44%;
            width: var(--size);
            height: var(--size);
            border-radius: var(--radius, 50%);
            background: var(--color, #e4bc70);
            box-shadow: 0 0 8px color-mix(in srgb, var(--color, #e4bc70) 60%, transparent);
            opacity: 0;
            animation: story-photo-dust var(--duration) ease-out forwards;
        }

        @keyframes story-photo-dust {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.2) rotate(0deg); }
            18% { opacity: 0.9; }
            100% {
                opacity: 0;
                transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(var(--scale)) rotate(var(--rot));
            }
        }

        @media (orientation: landscape) {
            .story-photo-image,
            .story-cover-image {
                object-fit: contain;
            }

            .story-open-copy {
                top: 22%;
                width: min(38vw, 460px);
            }

            .story-open-names {
                font-size: clamp(30px, 5vw, 58px);
            }

            .story-tap-hint {
                bottom: max(5vh, calc(env(safe-area-inset-bottom) + 18px));
            }
        }

        @media (max-width: 430px) and (min-height: 760px) {
            .story-open-copy {
                top: 28%;
                width: 72vw;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .story-tap-hint::before,
            .story-photo-particle,
            .story-is-opening .story-edge-glint,
            .story-is-opening .story-gutter-light,
            .story-photo-stage.story-is-opening::after {
                animation: none;
            }

            .story-photo-stage,
            .story-photo-media,
            .story-photo-image,
            .story-cover-panel,
            .story-cover-image,
            .story-open-copy,
            .story-tap-hint {
                transition-duration: 0.01ms !important;
                transition-delay: 0ms !important;
            }

            .story-is-opening .story-cover-panel {
                transform: none;
                opacity: 0;
            }
        }
    </style>

    <div
        class="story-photo-stage"
        id="story-photo-stage"
        wire:ignore
    >
        <button
            type="button"
            class="story-photo-trigger"
            id="story-photo-trigger"
            aria-label="{{ __('invitation.envelope_open') }}"
            aria-expanded="false"
        >
            <span class="story-photo-media" aria-hidden="true">
                <img
                    class="story-photo-image story-photo-open"
                    src="{{ $storyOpenUrl }}"
                    alt=""
                    loading="eager"
                    decoding="async"
                >

                <span class="story-cover-panel">
                    <img
                        class="story-cover-image"
                        src="{{ $storyClosedUrl }}"
                        alt=""
                        loading="eager"
                        decoding="async"
                        fetchpriority="high"
                    >
                </span>
            </span>

            <span class="story-edge-glint" aria-hidden="true"></span>
            <span class="story-gutter-light" aria-hidden="true"></span>
            <span class="story-photo-particles" id="story-photo-particles" aria-hidden="true"></span>

            <span class="story-open-copy">
                <span class="story-open-ornament" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 21c-5.4-3.65-8.8-6.5-8.8-10.45A4.55 4.55 0 0 1 12 8.92a4.55 4.55 0 0 1 8.8 1.63C20.8 14.5 17.4 17.35 12 21Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="story-open-eyebrow">{{ __('invitation.save_the_date') }}</span>
                <span class="story-open-names">{{ $event->couple_names }}</span>
                <span class="story-open-rule" aria-hidden="true"></span>
                @if ($storyMeta)
                    <span class="story-open-meta">{{ $storyMeta }}</span>
                @endif
            </span>

            <span class="story-tap-hint">{{ __('invitation.envelope_tap') }}</span>
        </button>
    </div>

    <script>
        (function () {
            const stage = document.getElementById('story-photo-stage');
            const trigger = document.getElementById('story-photo-trigger');
            const particleLayer = document.getElementById('story-photo-particles');

            if (!stage || !trigger) {
                return;
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const css = getComputedStyle(document.documentElement);
            const CROSSFADE = parseInt(css.getPropertyValue('--story-crossfade'), 10) || 1350;
            const HOLD = parseInt(css.getPropertyValue('--story-hold'), 10) || 900;
            const ZOOM = parseInt(css.getPropertyValue('--story-zoom'), 10) || 900;
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
                if (!particleLayer || particleLayer.childElementCount >= 22) {
                    return;
                }

                const particle = document.createElement('span');
                particle.className = 'story-photo-particle';
                const isPaper = index % 3 === 0;
                const size = (2 + Math.random() * (isPaper ? 4 : 5)).toFixed(1) + 'px';

                particle.style.setProperty('--size', size);
                particle.style.setProperty('--dx', (Math.random() * 220 - 110).toFixed(0) + 'px');
                particle.style.setProperty('--dy', (Math.random() * 180 - 120).toFixed(0) + 'px');
                particle.style.setProperty('--scale', (0.45 + Math.random() * 1).toFixed(2));
                particle.style.setProperty('--rot', (Math.random() * 120 - 60).toFixed(0) + 'deg');
                particle.style.setProperty('--duration', (0.85 + Math.random() * 0.75).toFixed(2) + 's');
                particle.style.setProperty('--color', isPaper ? '#fff7dc' : '#e4bc70');
                particle.style.setProperty('--radius', isPaper ? '12%' : '50%');

                particleLayer.appendChild(particle);
                particle.addEventListener('animationend', () => particle.remove(), { once: true });
            }

            function releaseParticles() {
                for (let index = 0; index < 20; index++) {
                    window.setTimeout(() => spawnParticle(index), index * 30);
                }
            }

            function beginOpening() {
                stage.classList.remove('story-is-pressing');
                stage.classList.add('story-is-opening');

                if (reduceMotion) {
                    window.setTimeout(showInviteContent, 180);
                    window.setTimeout(() => {
                        stage.classList.add('story-is-fading');
                        finishReveal();
                    }, 420);
                    return;
                }

                window.setTimeout(releaseParticles, 150);

                window.setTimeout(() => {
                    stage.classList.add('story-is-zooming');
                }, CROSSFADE + HOLD);

                window.setTimeout(showInviteContent, CROSSFADE + HOLD + 170);

                window.setTimeout(() => {
                    stage.classList.add('story-is-fading');
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

                stage.classList.add('story-is-pressing');

                window.setTimeout(beginOpening, reduceMotion ? 0 : 120);
            }

            trigger.addEventListener('click', reveal, { once: true });
        })();
    </script>
@endif

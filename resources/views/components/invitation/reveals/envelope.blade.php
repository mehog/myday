@if (! $isPreview)
    @php
        $envelopeDate = $event->wedding_date->format('d · m · Y');
        $envelopeMeta = collect([$envelopeDate, $event->location_name])->filter()->implode(' — ');

        $envAccents = [
            'amber-gold' => ['ink' => '#332516', 'gold' => '#b88b42'],
            'royal-wedding' => ['ink' => '#172944', 'gold' => '#b18b32'],
            'lavender-dream' => ['ink' => '#463653', 'gold' => '#9b7bb8'],
            'winter-magic' => ['ink' => '#20364b', 'gold' => '#6299ba'],
            'pearl-white' => ['ink' => '#33291f', 'gold' => '#8C7355'],
            'dusty-rose' => ['ink' => '#532f2c', 'gold' => '#B5706A'],
            'paper-ink' => ['ink' => '#3A2E24', 'gold' => '#9A7B4F'],
        ];

        $envAccent = $envAccents[$event->theme->value] ?? $envAccents['amber-gold'];
        $envelopeClosedUrl = asset('img/envelope-reveal/nasdan-envelope-closed.webp');
        $envelopeOpenUrl = asset('img/envelope-reveal/nasdan-envelope-open.webp');
    @endphp

    <style>
        :root {
            --env-crossfade: 1350;
            --env-hold: 900;
            --env-zoom: 900;
            --env-ink: {{ $envAccent['ink'] }};
            --env-gold: {{ $envAccent['gold'] }};
        }

        .env-photo-stage,
        .env-photo-stage * {
            box-sizing: border-box;
        }

        .env-photo-stage {
            --env-closed-image: url('{{ $envelopeClosedUrl }}');
            position: fixed;
            inset: 0;
            z-index: 100;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: hidden;
            isolation: isolate;
            background: #eee5d8;
            opacity: 1;
            transition: opacity 0.78s ease, filter 0.78s ease;
        }

        .env-photo-stage::before {
            content: '';
            position: absolute;
            z-index: -2;
            inset: -28px;
            background-image: var(--env-closed-image);
            background-position: center;
            background-size: cover;
            filter: blur(22px) saturate(0.72) brightness(0.9);
            transform: scale(1.08);
        }

        .env-photo-stage::after {
            content: '';
            position: absolute;
            z-index: 8;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 45%, rgba(255, 251, 239, 0.52), transparent 32%);
            opacity: 0;
            transition: opacity 0.55s ease;
        }

        .env-photo-stage.env-is-opening::after {
            animation: env-photo-flash 1.15s ease-out both;
        }

        .env-photo-stage.env-is-fading {
            opacity: 0;
            filter: brightness(1.08) blur(2px);
            pointer-events: none;
        }

        @keyframes env-photo-flash {
            0%, 100% { opacity: 0; }
            38% { opacity: 0.5; }
        }

        .env-photo-envelope {
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

        .env-photo-envelope:focus-visible {
            outline: 2px solid rgba(184, 139, 66, 0.95);
            outline-offset: -8px;
        }

        .env-photo-envelope[aria-expanded='true'] {
            cursor: default;
            pointer-events: none;
        }

        .env-photo-media {
            position: absolute;
            inset: 0;
            overflow: hidden;
            background: #f1e9dd;
            transform: scale(1);
            transform-origin: 50% 45%;
            transition: transform 0.95s cubic-bezier(0.65, 0, 0.35, 1), filter 0.7s ease;
            will-change: transform, filter;
        }

        .env-photo-media::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 4;
            pointer-events: none;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.08), transparent 25%),
                radial-gradient(ellipse at center, transparent 55%, rgba(67, 43, 25, 0.08) 100%);
        }

        .env-photo-image {
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

        .env-photo-closed {
            z-index: 2;
            opacity: 1;
            filter: brightness(1);
            transform: scale(1);
            transition:
                opacity 1.05s cubic-bezier(0.4, 0, 0.2, 1),
                transform 1.45s cubic-bezier(0.2, 0.75, 0.22, 1),
                filter 1s ease;
        }

        .env-photo-open {
            z-index: 1;
            opacity: 0;
            filter: brightness(1.06) blur(2px);
            transform: scale(1.035);
            transition:
                opacity 1.2s cubic-bezier(0.4, 0, 0.2, 1) 0.12s,
                transform 1.65s cubic-bezier(0.16, 1, 0.3, 1) 0.08s,
                filter 1.1s ease 0.08s;
        }

        .env-is-opening .env-photo-closed {
            opacity: 0;
            filter: brightness(1.12) blur(2px);
            transform: scale(1.025);
        }

        .env-is-opening .env-photo-open {
            opacity: 1;
            filter: brightness(1) blur(0);
            transform: scale(1);
        }

        .env-is-zooming .env-photo-media {
            filter: brightness(1.08);
            transform: scale(1.16);
        }

        .env-seal-light {
            position: absolute;
            z-index: 6;
            left: 50%;
            top: 44.5%;
            width: clamp(86px, 22vw, 160px);
            aspect-ratio: 1;
            border: 1px solid rgba(255, 243, 205, 0.68);
            border-radius: 50%;
            opacity: 0;
            pointer-events: none;
            box-shadow:
                0 0 34px rgba(236, 198, 123, 0.42),
                inset 0 0 26px rgba(255, 248, 221, 0.42);
            transform: translate(-50%, -50%) scale(0.35);
        }

        .env-is-opening .env-seal-light {
            animation: env-seal-release 0.95s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes env-seal-release {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.35); }
            32% { opacity: 0.9; }
            100% { opacity: 0; transform: translate(-50%, -50%) scale(1.65); }
        }

        .env-open-copy {
            position: absolute;
            z-index: 5;
            left: 50%;
            top: 29%;
            width: min(70vw, 520px);
            color: var(--env-ink);
            text-align: center;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.65);
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, 28px) scale(0.97);
            transition:
                opacity 0.9s ease 0.68s,
                transform 1.05s cubic-bezier(0.16, 1, 0.3, 1) 0.66s,
                filter 0.8s ease 0.66s;
            pointer-events: none;
        }

        .env-is-opening .env-open-copy {
            opacity: 1;
            filter: blur(0);
            transform: translate(-50%, 0) scale(1);
        }

        .env-is-zooming .env-open-copy {
            opacity: 0;
            filter: blur(3px);
            transform: translate(-50%, -12px) scale(1.12);
            transition:
                opacity 0.52s ease,
                transform 0.85s cubic-bezier(0.65, 0, 0.35, 1),
                filter 0.55s ease;
        }

        .env-open-ornament {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(10px, 2.2vw, 18px);
            margin-bottom: clamp(12px, 2.2vh, 22px);
            color: var(--env-gold);
        }

        .env-open-ornament::before,
        .env-open-ornament::after {
            content: '';
            width: clamp(34px, 10vw, 84px);
            height: 1px;
            background: linear-gradient(90deg, transparent, currentColor);
        }

        .env-open-ornament::after {
            background: linear-gradient(90deg, currentColor, transparent);
        }

        .env-open-ornament svg {
            width: clamp(15px, 3.2vw, 23px);
            height: auto;
        }

        .env-open-eyebrow,
        .env-open-names,
        .env-open-meta {
            display: block;
        }

        .env-open-eyebrow {
            margin-bottom: clamp(9px, 1.8vh, 16px);
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(9px, 2.2vw, 13px);
            font-weight: 500;
            line-height: 1.35;
            letter-spacing: clamp(3px, 0.8vw, 6px);
            text-transform: uppercase;
            color: var(--env-gold);
        }

        .env-open-names {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(34px, 9vw, 76px);
            font-weight: 600;
            font-style: italic;
            line-height: 1.03;
            text-wrap: balance;
        }

        .env-open-rule {
            display: block;
            width: clamp(62px, 16vw, 120px);
            height: 1px;
            margin: clamp(15px, 2.5vh, 24px) auto clamp(11px, 2vh, 18px);
            background: linear-gradient(90deg, transparent, var(--env-gold), transparent);
        }

        .env-open-meta {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(8px, 2vw, 12px);
            font-weight: 500;
            line-height: 1.55;
            letter-spacing: clamp(1.3px, 0.45vw, 3px);
            text-transform: uppercase;
            color: color-mix(in srgb, var(--env-ink) 72%, white);
        }

        .env-tap-hint {
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

        .env-tap-hint::before {
            content: '';
            width: 6px;
            height: 6px;
            flex: 0 0 auto;
            border-radius: 50%;
            background: var(--env-gold);
            animation: env-tap-pulse 1.9s ease-out infinite;
        }

        .env-is-opening .env-tap-hint {
            opacity: 0;
            transform: translate(-50%, 12px);
        }

        @keyframes env-tap-pulse {
            0% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--env-gold) 45%, transparent); }
            100% { box-shadow: 0 0 0 10px transparent; }
        }

        .env-photo-particles {
            position: absolute;
            z-index: 7;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .env-photo-particle {
            position: absolute;
            left: 50%;
            top: 45%;
            width: var(--size);
            height: var(--size);
            border-radius: 50%;
            background: #e4bc70;
            box-shadow: 0 0 10px rgba(228, 188, 112, 0.7);
            opacity: 0;
            animation: env-photo-dust var(--duration) ease-out forwards;
        }

        @keyframes env-photo-dust {
            0% { opacity: 0; transform: translate(-50%, -50%) scale(0.2); }
            18% { opacity: 0.9; }
            100% {
                opacity: 0;
                transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(var(--scale));
            }
        }

        @media (orientation: landscape) {
            .env-photo-image {
                object-fit: contain;
            }

            .env-open-copy {
                top: 22%;
                width: min(38vw, 460px);
            }

            .env-open-names {
                font-size: clamp(30px, 5vw, 58px);
            }

            .env-tap-hint {
                bottom: max(5vh, calc(env(safe-area-inset-bottom) + 18px));
            }
        }

        @media (max-width: 430px) and (min-height: 760px) {
            .env-open-copy {
                top: 27.5%;
                width: 72vw;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .env-tap-hint::before,
            .env-photo-particle,
            .env-is-opening .env-seal-light,
            .env-photo-stage.env-is-opening::after {
                animation: none;
            }

            .env-photo-stage,
            .env-photo-media,
            .env-photo-image,
            .env-open-copy,
            .env-tap-hint {
                transition-duration: 0.01ms !important;
                transition-delay: 0ms !important;
            }
        }
    </style>

    <div
        class="env-photo-stage"
        id="env-photo-stage"
        wire:ignore
    >
        <button
            type="button"
            class="env-photo-envelope"
            id="env-photo-envelope"
            aria-label="{{ __('invitation.envelope_open') }}"
            aria-expanded="false"
        >
            <span class="env-photo-media" aria-hidden="true">
                <img
                    class="env-photo-image env-photo-open"
                    src="{{ $envelopeOpenUrl }}"
                    alt=""
                    loading="eager"
                    decoding="async"
                >
                <img
                    class="env-photo-image env-photo-closed"
                    src="{{ $envelopeClosedUrl }}"
                    alt=""
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                >
            </span>

            <span class="env-seal-light" aria-hidden="true"></span>
            <span class="env-photo-particles" id="env-photo-particles" aria-hidden="true"></span>

            <span class="env-open-copy">
                <span class="env-open-ornament" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 21c-5.4-3.65-8.8-6.5-8.8-10.45A4.55 4.55 0 0 1 12 8.92a4.55 4.55 0 0 1 8.8 1.63C20.8 14.5 17.4 17.35 12 21Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="env-open-eyebrow">{{ __('invitation.save_the_date') }}</span>
                <span class="env-open-names">{{ $event->couple_names }}</span>
                <span class="env-open-rule" aria-hidden="true"></span>
                @if ($envelopeMeta)
                    <span class="env-open-meta">{{ $envelopeMeta }}</span>
                @endif
            </span>

            <span class="env-tap-hint">{{ __('invitation.envelope_tap') }}</span>
        </button>
    </div>

    <script>
        (function () {
            const stage = document.getElementById('env-photo-stage');
            const envelope = document.getElementById('env-photo-envelope');
            const particleLayer = document.getElementById('env-photo-particles');

            if (!stage || !envelope) {
                return;
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const css = getComputedStyle(document.documentElement);
            const CROSSFADE = parseInt(css.getPropertyValue('--env-crossfade'), 10) || 1350;
            const HOLD = parseInt(css.getPropertyValue('--env-hold'), 10) || 900;
            const ZOOM = parseInt(css.getPropertyValue('--env-zoom'), 10) || 900;
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
                particle.className = 'env-photo-particle';
                particle.style.setProperty('--size', (2 + Math.random() * 5).toFixed(1) + 'px');
                particle.style.setProperty('--dx', (Math.random() * 230 - 115).toFixed(0) + 'px');
                particle.style.setProperty('--dy', (Math.random() * 190 - 125).toFixed(0) + 'px');
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
                envelope.setAttribute('aria-expanded', 'true');

                // Keep audio playback directly inside the click gesture for browser autoplay rules.
                window.envYtPlayOnGesture?.();

                stage.classList.add('env-is-opening');

                if (reduceMotion) {
                    window.setTimeout(showInviteContent, 180);
                    window.setTimeout(() => {
                        stage.classList.add('env-is-fading');
                        finishReveal();
                    }, 420);
                    return;
                }

                window.setTimeout(releaseDust, 120);

                window.setTimeout(() => {
                    stage.classList.add('env-is-zooming');
                }, CROSSFADE + HOLD);

                window.setTimeout(showInviteContent, CROSSFADE + HOLD + 170);

                window.setTimeout(() => {
                    stage.classList.add('env-is-fading');
                    finishReveal();
                }, CROSSFADE + HOLD + ZOOM);
            }

            envelope.addEventListener('click', reveal, { once: true });
        })();
    </script>
@endif

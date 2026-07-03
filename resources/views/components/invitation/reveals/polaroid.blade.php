@if (! $isPreview)
    @php
        $poloHasImage = (bool) $event->hero_image_url;
        $poloDate = $event->wedding_date->format('d · m · Y');
        $poloMeta = collect([$poloDate, $event->location_name])->filter()->implode(' — ');

        $poloPalettes = [
            'amber-gold' => [
                '--polo-bg' => '#1a1208',
                '--polo-shadow' => 'rgba(0, 0, 0, 0.45)',
                '--polo-caption-bg' => '#fbf6ee',
                '--polo-caption-ink' => '#1a1208',
                '--polo-accent' => '#c9a227',
                '--polo-photo-bg' => '#f5e6c8',
            ],
            'royal-wedding' => [
                '--polo-bg' => '#0f1a2e',
                '--polo-shadow' => 'rgba(0, 0, 0, 0.5)',
                '--polo-caption-bg' => '#f8f6f0',
                '--polo-caption-ink' => '#0f1a2e',
                '--polo-accent' => '#d4af37',
                '--polo-photo-bg' => '#d4e0f0',
            ],
            'lavender-dream' => [
                '--polo-bg' => '#2d2438',
                '--polo-shadow' => 'rgba(0, 0, 0, 0.45)',
                '--polo-caption-bg' => '#faf8fc',
                '--polo-caption-ink' => '#2d2438',
                '--polo-accent' => '#c9b8e0',
                '--polo-photo-bg' => '#e8dff5',
            ],
            'winter-magic' => [
                '--polo-bg' => '#1a2332',
                '--polo-shadow' => 'rgba(0, 0, 0, 0.5)',
                '--polo-caption-bg' => '#f0f8ff',
                '--polo-caption-ink' => '#1a2332',
                '--polo-accent' => '#7eb8da',
                '--polo-photo-bg' => '#e8f4fc',
            ],
            'pearl-white' => [
                '--polo-bg' => '#6E5A42',
                '--polo-shadow' => 'rgba(0, 0, 0, 0.4)',
                '--polo-caption-bg' => '#FAFAF8',
                '--polo-caption-ink' => '#1C1917',
                '--polo-accent' => '#8C7355',
                '--polo-photo-bg' => '#E8E0D5',
            ],
            'dusty-rose' => [
                '--polo-bg' => '#9A5A55',
                '--polo-shadow' => 'rgba(0, 0, 0, 0.45)',
                '--polo-caption-bg' => '#F9F1EE',
                '--polo-caption-ink' => '#2D1B16',
                '--polo-accent' => '#B5706A',
                '--polo-photo-bg' => '#E8C9C4',
            ],
        ];

        $poloPalette = $poloPalettes[$event->theme->value] ?? $poloPalettes['amber-gold'];
    @endphp

    <style>
        :root {
            @foreach ($poloPalette as $var => $val)
                {{ $var }}: {{ $val }};
            @endforeach
        }

        .polo-stage {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: radial-gradient(
                120% 90% at 50% 8%,
                color-mix(in srgb, var(--polo-bg) 50%, white) 0%,
                color-mix(in srgb, var(--polo-bg) 72%, white) 42%,
                color-mix(in srgb, var(--polo-bg) 58%, white) 100%
            );
            font-family: 'Montserrat', sans-serif;
        }

        .polo-stage.polo-gone {
            opacity: 0;
            pointer-events: none;
        }

        .polo-stage.polo-opening {
            transition: opacity 0.3s ease 0.5s;
            opacity: 0;
            pointer-events: none;
        }

        .polo-card {
            position: relative;
            width: 220px;
            background: #fff;
            padding: 12px 12px 44px;
            box-shadow: 0 20px 60px var(--polo-shadow);
            border-radius: 2px;
            cursor: pointer;
            animation: polo-drop 0.75s cubic-bezier(0.22, 1, 0.36, 1) 0.2s both;
            transform-origin: center center;
        }

        @keyframes polo-drop {
            0% {
                transform: translateY(-120vh) rotate(-6deg);
                opacity: 0.6;
            }
            78% {
                transform: translateY(8px) rotate(1.5deg);
            }
            92% {
                transform: translateY(-4px) rotate(-0.5deg);
            }
            100% {
                transform: translateY(0) rotate(-2deg);
                opacity: 1;
            }
        }

        .polo-card.polo-landed {
            animation: polo-sway 6s ease-in-out infinite;
        }

        @keyframes polo-sway {
            0%, 100% {
                transform: rotate(-2deg);
            }
            50% {
                transform: rotate(1.5deg);
            }
        }

        .polo-stage.polo-opening .polo-card {
            animation: polo-zoom 0.65s cubic-bezier(0.5, 0, 0.85, 0.35) forwards;
        }

        @keyframes polo-zoom {
            0% {
                transform: rotate(-2deg) scale(1);
                opacity: 1;
            }
            100% {
                transform: rotate(6deg) scale(6);
                opacity: 0;
            }
        }

        .polo-photo {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            background: var(--polo-photo-bg);
        }

        .polo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .polo-photo-text {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 16px;
            color: var(--polo-caption-ink);
        }

        .polo-eyebrow {
            display: block;
            font-size: 9px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--polo-accent);
            margin-bottom: 8px;
        }

        .polo-names {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 600;
            font-style: italic;
            line-height: 1.2;
        }

        .polo-rule {
            width: 32px;
            height: 1px;
            background: var(--polo-accent);
            margin: 10px auto 0;
            opacity: 0.6;
        }

        .polo-caption {
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 10px;
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 13px;
            font-style: italic;
            color: var(--polo-caption-ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .polo-hint {
            margin-top: 2.5rem;
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            transition: opacity 0.3s ease;
        }

        .polo-stage.polo-opening .polo-hint {
            opacity: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            .polo-card {
                animation: none;
                transform: rotate(-2deg);
            }

            .polo-card.polo-landed {
                animation: none;
            }
        }
    </style>

    <div class="polo-stage" id="polo-stage" wire:ignore>
        <div
            class="polo-card"
            id="polo-card"
            role="button"
            tabindex="0"
            aria-label="{{ __('invitation.envelope_open') }}"
        >
            <div class="polo-photo">
                @if ($poloHasImage)
                    <img
                        src="{{ $event->hero_image_url }}"
                        alt="{{ $event->couple_names }}"
                        class="polo-img"
                    >
                @else
                    <div class="polo-photo-text">
                        <span class="polo-eyebrow">{{ __('invitation.save_the_date') }}</span>
                        <div class="polo-names">{{ $event->couple_names }}</div>
                        <div class="polo-rule"></div>
                    </div>
                @endif
            </div>
            <div class="polo-caption">
                @if ($poloHasImage)
                    {{ $event->couple_names }}
                @elseif ($poloMeta)
                    {{ $poloMeta }}
                @else
                    {{ $event->couple_names }}
                @endif
            </div>
        </div>
        <p class="polo-hint">{{ __('invitation.envelope_tap') }}</p>
    </div>

    <script>
        (function () {
            const card = document.getElementById('polo-card');
            const stage = document.getElementById('polo-stage');
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            let started = false;
            let revealedToLivewire = false;

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
                showInviteContent();
                document.dispatchEvent(new CustomEvent('invitation:revealed'));
            }

            card.addEventListener('animationend', function onDrop(event) {
                if (event.animationName !== 'polo-drop') {
                    return;
                }

                card.removeEventListener('animationend', onDrop);
                card.classList.add('polo-landed');
            });

            function reveal() {
                if (started) {
                    return;
                }

                started = true;
                window.envYtPlayOnGesture?.();

                if (reduce) {
                    stage.classList.add('polo-gone');
                    finishReveal();
                    return;
                }

                card.classList.remove('polo-landed');
                stage.classList.add('polo-opening');
                showInviteContent();

                setTimeout(() => {
                    stage.classList.add('polo-gone');
                    finishReveal();
                }, 800);
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

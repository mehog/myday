<section class="invitation-section py-16 px-6">
    <div class="max-w-lg mx-auto text-center invitation-fade-in">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-6">
            {{ __('invitation.our_song') }}
        </p>

        <div
            class="relative w-full overflow-hidden rounded-xl border border-white/10"
            style="padding-top: 56.25%;"
            wire:ignore
        >
            @if ($event->reveal_animation)
                <div id="env-yt-player" class="absolute inset-0 w-full h-full"></div>
            @else
                <iframe
                    src="{{ $event->youtube_embed_url }}"
                    class="absolute inset-0 w-full h-full"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    loading="lazy"
                    title="{{ __('invitation.our_song') }}"
                ></iframe>
            @endif
        </div>
    </div>
</section>

@if ($event->youtube_video_id && $event->reveal_animation)
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        (function () {
            let envYtPlayer = null;
            let playerReady = false;
            let pendingPlay = false;
            let initStarted = false;

            function playWhenReady() {
                if (playerReady && envYtPlayer && typeof envYtPlayer.playVideo === 'function') {
                    envYtPlayer.playVideo();
                    pendingPlay = false;
                    return;
                }

                pendingPlay = true;
            }

            function initYtPlayer() {
                if (initStarted || ! document.getElementById('env-yt-player')) {
                    return;
                }

                initStarted = true;

                envYtPlayer = new YT.Player('env-yt-player', {
                    videoId: @json($event->youtube_video_id),
                    playerVars: {
                        autoplay: 0,
                        controls: 1,
                        rel: 0,
                        modestbranding: 1,
                        fs: 0,
                        iv_load_policy: 3,
                    },
                    events: {
                        onReady: function () {
                            playerReady = true;
                            window.envYtPlayer = envYtPlayer;

                            if (pendingPlay) {
                                playWhenReady();
                            }
                        },
                    },
                });
            }

            function ensureApiThenInit() {
                if (window.YT && window.YT.Player) {
                    initYtPlayer();
                    return;
                }

                const prevCallback = window.onYouTubeIframeAPIReady;

                window.onYouTubeIframeAPIReady = function () {
                    if (typeof prevCallback === 'function') {
                        prevCallback();
                    }

                    initYtPlayer();
                };
            }

            document.addEventListener('invitation:revealed', function () {
                ensureApiThenInit();
                playWhenReady();
            });
        })();
    </script>
@endif

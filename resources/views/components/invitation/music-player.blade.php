<section class="invitation-section py-16 px-6">
    <div class="max-w-lg mx-auto text-center invitation-fade-in">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-6">
            {{ __('invitation.our_song') }}
        </p>

        <div
            class="relative w-full overflow-hidden rounded-xl border border-white/10"
            style="padding-top: 56.25%;"
        >
            <div id="env-yt-player" class="absolute inset-0 w-full h-full"></div>
        </div>
    </div>
</section>

@if ($event->youtube_video_id)
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        (function () {
            let envYtPlayer = null;
            let pendingPlay = false;

            function playEnvYoutube() {
                if (envYtPlayer && typeof envYtPlayer.playVideo === 'function') {
                    envYtPlayer.playVideo();
                    pendingPlay = false;
                    return;
                }

                pendingPlay = true;
            }

            window.onYouTubeIframeAPIReady = function () {
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
                            window.envYtPlayer = envYtPlayer;

                            if (pendingPlay) {
                                playEnvYoutube();
                            }
                        },
                    },
                });
            };

            document.addEventListener('invitation:revealed', playEnvYoutube);
        })();
    </script>
@endif

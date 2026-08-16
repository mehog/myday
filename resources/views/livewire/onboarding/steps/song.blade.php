<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.song_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-6 text-center text-sm">
        {{ __('onboarding.song_subtitle') }}
    </p>

    <div class="relative mb-6">
        <div class="h-[250px] overflow-y-auto overscroll-contain space-y-3 pr-1">
            @forelse ($songs as $song)
                <button
                    type="button"
                    wire:click="selectSong(@js($song['url']))"
                    data-song-pick
                    @class([
                        'w-full p-3 rounded-xl text-left border transition active:scale-[0.98] flex items-center gap-3',
                        'border-[#c9a227] bg-[#c9a227]/10' => $music_url === $song['url'],
                        'border-[#1a1208]/15 bg-white hover:border-[#1a1208]/30' => $music_url !== $song['url'],
                    ])
                >
                    <img
                        src="https://img.youtube.com/vi/{{ $song['id'] }}/hqdefault.jpg"
                        alt=""
                        class="w-14 h-14 rounded-lg object-cover shrink-0"
                        loading="lazy"
                    >
                    <span class="flex-1 min-w-0">
                        <span class="block landing-heading font-semibold text-[#1a1208] truncate">{{ $song['title'] }}</span>
                        <span class="block text-sm text-[#5c5246] truncate">{{ $song['artist'] }}</span>
                    </span>
                    <svg class="w-5 h-5 text-[#5c5246] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @empty
                <p class="text-sm text-center text-[#5c5246] py-8">{{ __('onboarding.song_no_results') }}</p>
            @endforelse
        </div>
        @if ($songs->isNotEmpty())
            <div
                class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-b from-transparent via-white/80 to-white"
                aria-hidden="true"
            ></div>
        @endif
    </div>

    <div class="mb-5">
        <label for="song_query" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.song_search') }}</label>
        <div class="flex gap-2">
            <input
                id="song_query"
                type="search"
                wire:model.live.debounce.300ms="song_query"
                wire:keydown.enter.prevent="applySongQuery"
                class="landing-input w-full"
                placeholder="{{ __('onboarding.song_search_placeholder') }}"
            >
        </div>
        <p class="mt-2 text-xs text-[#5c5246]">{{ __('onboarding.song_or_paste') }}</p>
        <input
            type="url"
            wire:model.live.debounce.400ms="music_url"
            class="landing-input w-full mt-2"
            placeholder="https://www.youtube.com/watch?v=..."
        >
        @error('music_url') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    @if ($songPreview)
        <div class="mb-5 flex gap-3 p-3 rounded-xl border border-[#c9a227]/40 bg-[#c9a227]/5">
            @if ($songPreview['thumbnail_url'])
                <img src="{{ $songPreview['thumbnail_url'] }}" alt="" class="w-16 h-16 rounded-lg object-cover shrink-0">
            @endif
            <div class="min-w-0">
                <p class="text-sm font-medium text-[#1a1208] truncate">{{ $songPreview['title'] }}</p>
                <p class="text-xs text-[#5c5246] mt-1 truncate">{{ $music_url }}</p>
            </div>
        </div>
    @endif

    @if ($music_url !== '')
        <button type="button" wire:click="nextStep" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition">
            {{ __('onboarding.next') }}
        </button>
    @endif
</div>

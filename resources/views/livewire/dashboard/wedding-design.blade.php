@php
    $controlClass = 'block w-full rounded-md border border-border bg-background px-3 py-2 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif

    @if (! $wedding)
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.no_wedding') }}</p>
        </x-dashboard.card>
    @else
        @if ($locked)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                {{ __('app.wedding_archived_readonly') }}
            </div>
        @endif

        <form wire:submit="save" class="space-y-6">
            <x-dashboard.card>
                <h3 class="mb-4 font-medium">{{ __('app.section_design') }}</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium">{{ __('app.template') }}</label>
                        <x-dashboard.pills
                            name="template"
                            :selected="$template"
                            :options="collect($templates)->mapWithKeys(fn ($templateOption) => [$templateOption->value => $templateOption->label()])->all()"
                            :disabled="$locked"
                            :label="__('app.template')"
                        />
                        @error('template') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('app.theme') }}</label>
                        <select wire:model="theme" class="{{ $controlClass }} h-10" @disabled($locked)>
                            @foreach ($themes as $themeOption)
                                <option value="{{ $themeOption->value }}">{{ $themeOption->label() }}</option>
                            @endforeach
                        </select>
                        @error('theme') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('app.reveal_animation') }}</label>
                        <select wire:model="reveal_animation" class="{{ $controlClass }} h-10" @disabled($locked)>
                            <option value="">{{ __('app.reveal_none') }}</option>
                            @foreach ($reveals as $reveal)
                                <option value="{{ $reveal->value }}">{{ $reveal->label() }}</option>
                            @endforeach
                        </select>
                        @error('reveal_animation') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">{{ __('app.hero_image') }}</label>
                        @if ($wedding->hero_image_url && ! $removeHero && ! $heroUpload)
                            <img src="{{ $wedding->hero_image_url }}" alt="" class="mb-3 h-40 w-full max-w-md rounded-lg object-cover">
                        @endif
                        @if ($heroUpload)
                            <img src="{{ $heroUpload->temporaryUrl() }}" alt="" class="mb-3 h-40 w-full max-w-md rounded-lg object-cover">
                        @endif
                        <input type="file" wire:model="heroUpload" accept="image/*" class="block w-full text-sm" @disabled($locked)>
                        <div wire:loading wire:target="heroUpload" class="mt-1 text-xs text-muted-foreground">…</div>
                        @error('heroUpload') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                        @if (! $locked && ($wedding->hero_image_url || $heroUpload))
                            <button type="button" class="mt-2 text-sm text-red-600 hover:underline" wire:click="clearHero">
                                {{ __('dashboard.remove_hero') }}
                            </button>
                        @endif
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('app.youtube_song') }}</label>
                        <input type="url" wire:model="music_url" class="{{ $controlClass }} h-10" @disabled($locked)>
                        <p class="mt-1 text-xs text-muted-foreground">{{ __('app.youtube_helper') }}</p>
                        @error('music_url') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">{{ __('app.motto') }}</label>
                        <textarea wire:model="motto" rows="3" maxlength="300" class="{{ $controlClass }}" @disabled($locked)></textarea>
                        <p class="mt-1 text-xs text-muted-foreground">{{ __('app.motto_helper') }}</p>
                        @error('motto') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                </div>
            </x-dashboard.card>

            @unless ($locked)
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            @endunless
        </form>
    @endif
</div>

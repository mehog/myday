<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.schedule_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-5 text-center text-sm">
        {{ __('onboarding.schedule_subtitle') }}
    </p>

    <div class="flex flex-wrap gap-2 mb-5 justify-center">
        @foreach ($schedulePresets as $preset)
            <button
                type="button"
                wire:click="applySchedulePreset(@js($preset['time']), @js(__('onboarding.'.$preset['label_key'])))"
                class="text-xs px-3 py-1.5 rounded-full border border-[#1a1208]/15 bg-white hover:border-[#c9a227] text-[#5c5246]"
            >
                {{ $preset['time'] }} · {{ __('onboarding.'.$preset['label_key']) }}
            </button>
        @endforeach
    </div>

    <form wire:submit="nextStep" class="space-y-4">
        @foreach ($scheduleItems as $index => $item)
            <div class="p-4 rounded-xl border border-[#1a1208]/10 bg-[#fafaf8] space-y-3" wire:key="schedule-{{ $index }}">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs uppercase tracking-wider text-[#c9a227]">{{ __('onboarding.schedule_item') }} {{ $index + 1 }}</p>
                    @if (count($scheduleItems) > 1)
                        <button type="button" wire:click="removeScheduleItem({{ $index }})" class="text-xs text-red-500 hover:underline">
                            {{ __('onboarding.remove') }}
                        </button>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm text-[#5c5246] mb-1">{{ __('onboarding.schedule_time') }}</label>
                        <input type="time" wire:model="scheduleItems.{{ $index }}.time" class="landing-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm text-[#5c5246] mb-1">{{ __('onboarding.schedule_item_title') }}</label>
                        <input type="text" wire:model="scheduleItems.{{ $index }}.title" class="landing-input w-full">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-[#5c5246] mb-1">{{ __('onboarding.schedule_description') }}</label>
                    <input type="text" wire:model="scheduleItems.{{ $index }}.description" class="landing-input w-full">
                </div>
            </div>
        @endforeach

        <button type="button" wire:click="addScheduleItem" class="w-full landing-btn-secondary py-3 rounded-xl text-sm">
            {{ __('onboarding.schedule_add') }}
        </button>

        <button type="submit" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition">
            {{ __('onboarding.next') }}
        </button>
    </form>
</div>

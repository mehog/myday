@props([
    'selectClass' => 'landing-input text-sm py-1.5 px-3 min-w-[9rem] cursor-pointer',
    'labelClass' => 'text-sm text-[#d4c4a8]',
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2 justify-center']) }}>
    <label for="locale-picker" class="{{ $labelClass }}">{{ __('locale.label') }}</label>
    <select
        id="locale-picker"
        wire:change="switchLocale($event.target.value)"
        class="{{ $selectClass }}"
    >
        @foreach (\App\Support\Locale::options() as $code => $label)
            <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $label }}</option>
        @endforeach
    </select>
</div>

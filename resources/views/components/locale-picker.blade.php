@props([
    'selectClass' => 'landing-input text-sm py-1.5 px-3 min-w-[9rem] cursor-pointer',
    'labelClass' => 'text-sm text-[#5c5246]',
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2 justify-center']) }}>
    <label for="locale-picker" class="{{ $labelClass }}">{{ __('locale.label') }}</label>
    <select
        id="locale-picker"
        name="locale"
        class="{{ $selectClass }}"
        onchange="const url = new URL(window.location.href); url.searchParams.set('locale', this.value); window.location.assign(url.toString());"
    >
        @foreach (\App\Support\Locale::options() as $code => $label)
            <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $label }}</option>
        @endforeach
    </select>
</div>

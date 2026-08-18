@props([
    'lead',
    'emphasis',
    'tail' => '',
    'tag' => 'h2',
])

<{{ $tag }} {{ $attributes->merge(['class' => 'landing-heading']) }}>
    {{ $lead }} <em class="landing-emphasis">{{ $emphasis }}</em>@if ($tail !== '') {{ $tail }}@endif
</{{ $tag }}>

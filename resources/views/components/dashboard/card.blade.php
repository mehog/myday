<div {{ $attributes->merge(['class' => 'rounded-xl border border-border bg-card text-card-foreground shadow-sm']) }}>
    @isset($header)
        <div class="flex items-start justify-between gap-3 border-b border-border px-4 py-3 md:px-5">
            {{ $header }}
        </div>
    @endisset
    <div @class(['p-4 md:p-5' => ! isset($flush)])>
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="border-t border-border px-4 py-3 md:px-5">
            {{ $footer }}
        </div>
    @endisset
</div>

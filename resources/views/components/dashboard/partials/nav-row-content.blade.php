<span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted text-foreground">
    <x-dashboard.icon :name="$item['icon']" class="h-5 w-5" />
    @if (! empty($item['badge']))
        <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-semibold text-primary-foreground">
            {{ $item['badge'] > 9 ? '9+' : $item['badge'] }}
        </span>
    @endif
</span>
<span class="min-w-0 flex-1">
    <span class="block text-sm font-medium">{{ $item['label'] }}</span>
    @if (! empty($item['description']))
        <span class="mt-0.5 block text-xs text-muted-foreground">{{ $item['description'] }}</span>
    @endif
</span>
<x-dashboard.icon name="chevron-right" class="h-4 w-4 shrink-0 text-muted-foreground" />

<div
    class="relative"
    x-data="{ open: false }"
    wire:poll.30s
>
    <button
        type="button"
        class="relative inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-background hover:bg-accent"
        @click="open = !open"
        aria-label="{{ __('dashboard.notifications_title') }}"
        :aria-expanded="open"
    >
        <x-dashboard.icon name="bell" class="h-4 w-4" />
        @if ($unreadCount > 0)
            <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-semibold text-primary-foreground">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        @click.outside="open = false"
        x-cloak
        class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-md border border-border bg-popover shadow-lg"
    >
        <div class="flex items-center justify-between gap-2 border-b border-border px-3 py-2">
            <p class="text-sm font-medium">{{ __('dashboard.notifications_title') }}</p>
            @if ($unreadCount > 0)
                <button type="button" class="text-xs text-muted-foreground hover:text-foreground" wire:click="markAllAsRead">
                    {{ __('dashboard.notifications_mark_all') }}
                </button>
            @endif
        </div>

        @if ($items === [])
            <p class="px-3 py-6 text-center text-sm text-muted-foreground">{{ __('dashboard.notifications_empty') }}</p>
        @else
            <ul class="max-h-80 overflow-y-auto py-1">
                @foreach ($items as $item)
                    <li>
                        <button
                            type="button"
                            class="block w-full px-3 py-2 text-left hover:bg-accent"
                            wire:click="openNotification('{{ $item['id'] }}')"
                            @click="open = false"
                        >
                            <p @class(['text-sm', 'font-semibold' => $item['unread'], 'font-medium' => ! $item['unread']])>
                                {{ $item['title'] !== '' ? $item['title'] : __('dashboard.notifications_title') }}
                            </p>
                            @if ($item['body'] !== '')
                                <p class="mt-0.5 line-clamp-2 text-xs text-muted-foreground">{{ $item['body'] }}</p>
                            @endif
                            @if ($item['created'] !== '')
                                <p class="mt-1 text-[11px] text-muted-foreground">{{ $item['created'] }}</p>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<div class="space-y-5 lg:space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div class="hidden lg:block">
            <h2 class="text-xl font-semibold tracking-tight">{{ __('dashboard.notifications_title') }}</h2>
        </div>
        @if ($unreadCount > 0)
            <x-dashboard.button type="button" variant="secondary" wire:click="markAllAsRead" class="ml-auto lg:ml-0">
                {{ __('dashboard.notifications_mark_all') }}
            </x-dashboard.button>
        @endif
    </div>

    @if ($items === [])
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.notifications_empty') }}</p>
        </x-dashboard.card>
    @else
        <x-dashboard.list-group>
            @foreach ($items as $item)
                <button
                    type="button"
                    class="flex w-full items-start gap-3 px-4 py-3.5 text-left transition-colors hover:bg-muted/60"
                    wire:click="openNotification('{{ $item['id'] }}')"
                >
                    <span @class([
                        'mt-1.5 h-2 w-2 shrink-0 rounded-full',
                        'bg-primary' => $item['unread'],
                        'bg-transparent' => ! $item['unread'],
                    ])></span>
                    <span class="min-w-0 flex-1">
                        <span @class(['block text-sm', 'font-semibold' => $item['unread'], 'font-medium' => ! $item['unread']])>
                            {{ $item['title'] !== '' ? $item['title'] : __('dashboard.notifications_title') }}
                        </span>
                        @if ($item['body'] !== '')
                            <span class="mt-0.5 block text-xs text-muted-foreground">{{ $item['body'] }}</span>
                        @endif
                        @if ($item['created'] !== '')
                            <span class="mt-1 block text-[11px] text-muted-foreground">{{ $item['created'] }}</span>
                        @endif
                    </span>
                    <x-dashboard.icon name="chevron-right" class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                </button>
            @endforeach
        </x-dashboard.list-group>
    @endif
</div>

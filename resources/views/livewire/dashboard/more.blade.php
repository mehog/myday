<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold tracking-tight">{{ __('dashboard.more_title') }}</h2>
        <p class="mt-1 text-sm text-muted-foreground">{{ __('dashboard.more_subtitle') }}</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        @foreach ($items as $item)
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'flex items-center gap-3 border-b border-border px-4 py-3.5 transition-colors last:border-b-0',
                    'bg-accent/60' => \App\Support\DashboardNav::isActive($item),
                    'hover:bg-muted/60' => ! \App\Support\DashboardNav::isActive($item),
                ])
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted text-foreground">
                    <x-dashboard.icon :name="$item['icon']" class="h-5 w-5" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-medium">{{ $item['label'] }}</span>
                    @if (! empty($item['description']))
                        <span class="mt-0.5 block text-xs text-muted-foreground">{{ $item['description'] }}</span>
                    @endif
                </span>
                <x-dashboard.icon name="chevron-right" class="h-4 w-4 shrink-0 text-muted-foreground" />
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-border px-4 py-3.5">
            <div class="min-w-0">
                <p class="text-sm font-medium">{{ __('dashboard.appearance') }}</p>
                <p class="mt-0.5 text-xs text-muted-foreground">{{ __('dashboard.more_desc_appearance') }}</p>
            </div>
            <x-dashboard.appearance-toggle />
        </div>

        <a
            href="/app"
            class="flex items-center gap-3 border-b border-border px-4 py-3.5 transition-colors hover:bg-muted/60"
        >
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted text-foreground">
                <x-dashboard.icon name="panel" class="h-5 w-5" />
            </span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-medium">{{ __('dashboard.classic_app') }}</span>
                <span class="mt-0.5 block text-xs text-muted-foreground">{{ __('dashboard.more_desc_classic') }}</span>
            </span>
            <x-dashboard.icon name="chevron-right" class="h-4 w-4 shrink-0 text-muted-foreground" />
        </a>

        <form method="POST" action="{{ route('dashboard.logout') }}">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 px-4 py-3.5 text-left transition-colors hover:bg-muted/60"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted text-foreground">
                    <x-dashboard.icon name="logout" class="h-5 w-5" />
                </span>
                <span class="min-w-0 flex-1 text-sm font-medium">{{ __('dashboard.nav.logout') }}</span>
            </button>
        </form>
    </div>

    <div class="flex items-center gap-3 rounded-2xl border border-border bg-card px-4 py-3.5 shadow-sm">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-muted-foreground">{{ auth()->user()->email }}</p>
        </div>
    </div>

    <div class="dashboard-support-bubble-host">
        @include('components.app.support-bubble')
    </div>
</div>

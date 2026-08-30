<nav
    class="dashboard-bottom-nav fixed inset-x-0 bottom-0 z-40 border-t border-border bg-card/95 backdrop-blur lg:hidden"
    aria-label="{{ __('dashboard.bottom_nav_label') }}"
>
    <div class="mx-auto flex h-16 max-w-lg items-stretch justify-between px-1">
        @foreach (\App\Support\DashboardNav::tabItems() as $item)
            @php $active = \App\Support\DashboardNav::isActive($item); @endphp
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'dashboard-tab flex min-h-12 min-w-0 flex-1 flex-col items-center justify-center gap-0.5 px-1 text-[11px] font-medium leading-tight transition-colors',
                    'text-primary' => $active,
                    'text-muted-foreground' => ! $active,
                ])
                @if ($active) aria-current="page" @endif
            >
                <x-dashboard.icon :name="$item['icon']" class="h-6 w-6" />
                <span class="max-w-full truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>

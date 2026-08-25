<nav
    class="dashboard-bottom-nav fixed inset-x-0 bottom-0 z-40 border-t border-border bg-card/95 backdrop-blur lg:hidden"
    aria-label="{{ __('dashboard.bottom_nav_label') }}"
>
    <div class="mx-auto flex h-14 max-w-lg items-stretch justify-between px-1">
        @foreach (\App\Support\DashboardNav::tabItems() as $item)
            @php $active = \App\Support\DashboardNav::isActive($item); @endphp
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'dashboard-tab flex min-w-0 flex-1 flex-col items-center justify-center gap-0.5 px-1 text-[10px] font-medium leading-tight transition-colors',
                    'text-primary' => $active,
                    'text-muted-foreground hover:text-foreground' => ! $active,
                ])
                @if ($active) aria-current="page" @endif
            >
                <span @class([
                    'flex h-7 w-7 items-center justify-center rounded-full transition-colors',
                    'bg-primary/15' => $active,
                ])>
                    <x-dashboard.icon :name="$item['icon']" class="h-5 w-5" />
                </span>
                <span class="max-w-full truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>

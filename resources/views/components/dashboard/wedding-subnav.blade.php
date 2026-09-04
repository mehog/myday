@if (\App\Support\DashboardNav::isWeddingSection())
    <div class="dashboard-wedding-subnav -mx-1 mb-4 hidden overflow-x-auto px-1 lg:mx-0 lg:block">
        <div class="dashboard-pills !max-w-none">
            @foreach (\App\Support\DashboardNav::weddingSubItems() as $item)
                @php $active = \App\Support\DashboardNav::isActive($item); @endphp
                <a
                    href="{{ route($item['route']) }}"
                    @class([
                        'dashboard-pill inline-flex items-center gap-1.5 no-underline',
                        'is-active' => $active,
                    ])
                    @if ($active) aria-current="page" @endif
                >
                    <x-dashboard.icon :name="$item['icon']" class="h-3.5 w-3.5" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
@endif

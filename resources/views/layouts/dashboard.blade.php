<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full"
    x-data
    x-bind:class="{ dark: $store.dashboardAppearance.resolved === 'dark' }"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@isset($title){{ $title }} — @endisset{{ config('app.name') }}</title>

    <script>
        (function () {
            const appearance = localStorage.getItem('dashboard_appearance') || 'system';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (appearance === 'dark' || (appearance === 'system' && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @include('components.google-analytics')
    @include('components.meta-pixel')
    @include('components.app.disable-mobile-zoom')

    @vite(['resources/css/dashboard.css', 'resources/js/dashboard.js'])
    @livewireStyles
</head>
<body class="dashboard-body h-full overflow-hidden">
    <div
        class="flex h-full min-h-0"
        x-data="{
            sidebarOpen: localStorage.getItem('dashboard_sidebar') !== '0',
            mobileOpen: false,
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
                localStorage.setItem('dashboard_sidebar', this.sidebarOpen ? '1' : '0');
            }
        }"
    >
        {{-- Mobile overlay --}}
        <div
            x-show="mobileOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
            @click="mobileOpen = false"
            style="display: none;"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 flex h-full flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-[width,transform] duration-200 lg:static lg:translate-x-0"
            :class="[
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                sidebarOpen ? 'w-64' : 'w-64 lg:w-16'
            ]"
        >
            <div class="flex h-14 shrink-0 items-center gap-2 border-b border-sidebar-border px-3">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2 overflow-hidden rounded-md px-1 py-1.5 hover:bg-sidebar-accent">
                    <img src="{{ asset('icons/nd-logo-transparent.webp') }}" alt="{{ config('app.name') }}" class="h-8 w-8 shrink-0 object-contain">
                    <span class="truncate text-sm font-semibold" x-show="sidebarOpen || window.innerWidth < 1024" x-cloak>{{ config('app.name') }}</span>
                </a>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-2">
                @foreach (\App\Support\DashboardNav::mainItems() as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @click="mobileOpen = false"
                        @class([
                            'flex items-center gap-3 rounded-md px-2.5 py-2 text-sm font-medium transition-colors',
                            'bg-sidebar-accent text-sidebar-accent-foreground' => \App\Support\DashboardNav::isActive($item),
                            'text-sidebar-foreground/80 hover:bg-sidebar-accent/70 hover:text-sidebar-accent-foreground' => ! \App\Support\DashboardNav::isActive($item),
                        ])
                        title="{{ $item['label'] }}"
                    >
                        <x-dashboard.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                        <span class="truncate" x-show="sidebarOpen || window.innerWidth < 1024" x-cloak>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="space-y-1 border-t border-sidebar-border p-2">
                @foreach (\App\Support\DashboardNav::footerItems() as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @click="mobileOpen = false"
                        @class([
                            'flex items-center gap-3 rounded-md px-2.5 py-2 text-sm font-medium transition-colors',
                            'bg-sidebar-accent text-sidebar-accent-foreground' => \App\Support\DashboardNav::isActive($item),
                            'text-sidebar-foreground/80 hover:bg-sidebar-accent/70 hover:text-sidebar-accent-foreground' => ! \App\Support\DashboardNav::isActive($item),
                        ])
                        title="{{ $item['label'] }}"
                    >
                        <x-dashboard.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                        <span class="truncate" x-show="sidebarOpen || window.innerWidth < 1024" x-cloak>{{ $item['label'] }}</span>
                    </a>
                @endforeach

                <div class="mt-1 border-t border-sidebar-border pt-2" x-show="sidebarOpen || window.innerWidth < 1024" x-cloak>
                    <div class="flex items-center gap-2 px-2.5 py-2">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('dashboard.logout') }}" class="px-1">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-md px-2.5 py-2 text-sm text-muted-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                            <x-dashboard.icon name="logout" class="h-5 w-5 shrink-0" />
                            <span>{{ __('dashboard.nav.logout') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-14 shrink-0 items-center gap-3 border-b border-border bg-card/80 px-4 backdrop-blur">
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-background hover:bg-accent lg:hidden"
                    @click="mobileOpen = true"
                    aria-label="{{ __('dashboard.open_menu') }}"
                >
                    <x-dashboard.icon name="menu" class="h-5 w-5" />
                </button>
                <button
                    type="button"
                    class="hidden h-9 w-9 items-center justify-center rounded-md border border-border bg-background hover:bg-accent lg:inline-flex"
                    @click="toggleSidebar()"
                    aria-label="{{ __('dashboard.toggle_sidebar') }}"
                >
                    <x-dashboard.icon name="panel" class="h-5 w-5" />
                </button>

                <div class="min-w-0 flex-1">
                    @isset($breadcrumbs)
                        <nav class="flex items-center gap-1.5 text-sm text-muted-foreground">
                            @foreach ($breadcrumbs as $crumb)
                                @if (! $loop->last && ! empty($crumb['url']))
                                    <a href="{{ $crumb['url'] }}" class="hover:text-foreground">{{ $crumb['label'] }}</a>
                                    <span>/</span>
                                @else
                                    <span class="truncate font-medium text-foreground">{{ $crumb['label'] }}</span>
                                @endif
                            @endforeach
                        </nav>
                    @else
                        <h1 class="truncate text-sm font-semibold">@isset($title){{ $title }}@else{{ __('dashboard.nav.overview') }}@endisset</h1>
                    @endisset
                </div>

                <div class="flex items-center gap-2">
                    @livewire(\App\Livewire\Dashboard\NotificationsBell::class)
                    <x-dashboard.appearance-toggle />
                    <a
                        href="/app"
                        class="inline-flex shrink-0 rounded-md border border-border px-2.5 py-1.5 text-xs font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                    >
                        {{ __('dashboard.classic_app') }}
                    </a>
                </div>
            </header>

            <main class="min-h-0 flex-1 overflow-y-auto p-4 md:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @include('components.app.push-notifications')
    @include('components.app.support-bubble')
    @include('components.app.upgrade-required-modal')

    @livewireScripts
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full"
    x-data
    x-bind:class="{ dark: $store.dashboardAppearance.resolved === 'dark' }"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
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
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
                localStorage.setItem('dashboard_sidebar', this.sidebarOpen ? '1' : '0');
            }
        }"
    >
        {{-- Desktop sidebar --}}
        <aside
            class="hidden h-full shrink-0 flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-[width] duration-200 lg:flex"
            :class="sidebarOpen ? 'w-64' : 'w-16'"
        >
            <div class="flex h-14 shrink-0 items-center gap-2 border-b border-sidebar-border px-3">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2 overflow-hidden rounded-md px-1 py-1.5 hover:bg-sidebar-accent">
                    <img src="{{ asset('icons/nd-logo-transparent.webp') }}" alt="{{ config('app.name') }}" class="h-8 w-8 shrink-0 object-contain">
                    <span class="truncate text-sm font-semibold" x-show="sidebarOpen" x-cloak>{{ config('app.name') }}</span>
                </a>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-2">
                @foreach (\App\Support\DashboardNav::mainItems() as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'flex items-center gap-3 rounded-md px-2.5 py-2 text-sm font-medium transition-colors',
                            'bg-sidebar-accent text-sidebar-accent-foreground' => \App\Support\DashboardNav::isActive($item),
                            'text-sidebar-foreground/80 hover:bg-sidebar-accent/70 hover:text-sidebar-accent-foreground' => ! \App\Support\DashboardNav::isActive($item),
                        ])
                        title="{{ $item['label'] }}"
                    >
                        <x-dashboard.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                        <span class="truncate" x-show="sidebarOpen" x-cloak>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="space-y-1 border-t border-sidebar-border p-2">
                @foreach (\App\Support\DashboardNav::footerItems() as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'flex items-center gap-3 rounded-md px-2.5 py-2 text-sm font-medium transition-colors',
                            'bg-sidebar-accent text-sidebar-accent-foreground' => \App\Support\DashboardNav::isActive($item),
                            'text-sidebar-foreground/80 hover:bg-sidebar-accent/70 hover:text-sidebar-accent-foreground' => ! \App\Support\DashboardNav::isActive($item),
                        ])
                        title="{{ $item['label'] }}"
                    >
                        <x-dashboard.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                        <span class="truncate" x-show="sidebarOpen" x-cloak>{{ $item['label'] }}</span>
                    </a>
                @endforeach

                <div class="mt-1 border-t border-sidebar-border pt-2" x-show="sidebarOpen" x-cloak>
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
            @php
                $pageTitle = $title
                    ?? (isset($breadcrumbs) ? (collect($breadcrumbs)->last()['label'] ?? null) : null)
                    ?? __('dashboard.nav.overview');
                $hasBack = filled($backUrl ?? null);
                $hideMobileTitle = (bool) ($largeTitle ?? false);
                $isMobileRootTab = \App\Support\DashboardNav::isMobileRootTab();
            @endphp
            <header @class([
                'dashboard-topbar relative z-40 flex h-14 shrink-0 items-center gap-2 border-b border-border bg-card/80 px-3 backdrop-blur sm:gap-3 sm:px-4',
                'hidden lg:flex' => $isMobileRootTab,
            ])>
                <button
                    type="button"
                    class="hidden h-9 w-9 items-center justify-center rounded-md border border-border bg-background hover:bg-accent lg:inline-flex"
                    @click="toggleSidebar()"
                    aria-label="{{ __('dashboard.toggle_sidebar') }}"
                >
                    <x-dashboard.icon name="panel" class="h-5 w-5" />
                </button>

                @if ($hasBack)
                    <a
                        href="{{ $backUrl }}"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-primary hover:bg-accent lg:hidden"
                        aria-label="{{ __('dashboard.back') }}"
                    >
                        <x-dashboard.icon name="chevron-left" class="h-6 w-6" />
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="shrink-0 lg:hidden">
                        <img src="{{ asset('icons/nd-logo-transparent.webp') }}" alt="{{ config('app.name') }}" class="h-8 w-8 object-contain">
                    </a>
                @endif

                <div class="min-w-0 flex-1">
                    @isset($breadcrumbs)
                        <nav class="hidden items-center gap-1.5 text-sm text-muted-foreground lg:flex">
                            @foreach ($breadcrumbs as $crumb)
                                @if (! $loop->last && ! empty($crumb['url']))
                                    <a href="{{ $crumb['url'] }}" class="hover:text-foreground">{{ $crumb['label'] }}</a>
                                    <span>/</span>
                                @else
                                    <span class="truncate font-medium text-foreground">{{ $crumb['label'] }}</span>
                                @endif
                            @endforeach
                        </nav>
                    @endif
                    <h1 @class([
                        'truncate text-[17px] font-semibold tracking-tight lg:hidden',
                        'sr-only' => $hideMobileTitle && ! $hasBack,
                    ])>
                        {{ $pageTitle }}
                    </h1>
                    @unless(isset($breadcrumbs))
                        <h1 class="hidden truncate text-sm font-semibold lg:block">{{ $pageTitle }}</h1>
                    @endunless
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    @isset($topbarActions)
                        <div class="flex items-center gap-1 lg:hidden">
                            {{ $topbarActions }}
                        </div>
                    @endisset
                    <div class="hidden lg:block">
                        @livewire(\App\Livewire\Dashboard\NotificationsBell::class)
                    </div>
                    <div class="hidden lg:block">
                        <x-dashboard.appearance-toggle />
                    </div>
                    @unless ($hasBack)
                        <a
                            href="{{ route('dashboard.more') }}"
                            class="hidden h-9 w-9 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground lg:inline-flex"
                            aria-label="{{ __('dashboard.nav.more') }}"
                        >
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </a>
                    @endunless
                </div>
            </header>

            <main @class([
                'dashboard-main min-h-0 flex-1 overflow-y-auto px-3 py-4 md:p-6',
                'dashboard-main-mobile-root' => $isMobileRootTab,
            ])>
                <x-dashboard.wedding-subnav />
                @auth
                    @if (! auth()->user()->hasVerifiedEmail())
                        @livewire(\App\Livewire\Dashboard\EmailVerificationBanner::class)
                    @endif
                @endauth
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-dashboard.bottom-nav />

    @include('components.app.push-notifications')
    @include('components.app.upgrade-required-modal')

    @livewireScripts
    @stack('scripts')
</body>
</html>

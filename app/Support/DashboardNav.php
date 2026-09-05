<?php

namespace App\Support;

class DashboardNav
{
    /**
     * Full desktop sidebar navigation.
     *
     * @return list<array{label: string, route: string, icon: string, match: list<string>}>
     */
    public static function mainItems(): array
    {
        return [
            [
                'label' => __('dashboard.nav.overview'),
                'route' => 'dashboard',
                'icon' => 'home',
                'match' => ['dashboard'],
            ],
            [
                'label' => __('dashboard.nav.checklist'),
                'route' => 'dashboard.checklist',
                'icon' => 'checklist',
                'match' => ['dashboard.checklist'],
            ],
            [
                'label' => __('dashboard.nav.wedding'),
                'route' => 'dashboard.wedding',
                'icon' => 'heart',
                'match' => ['dashboard.wedding'],
            ],
            [
                'label' => __('dashboard.nav.locations'),
                'route' => 'dashboard.locations',
                'icon' => 'map',
                'match' => ['dashboard.locations'],
            ],
            [
                'label' => __('dashboard.nav.menus'),
                'route' => 'dashboard.menus',
                'icon' => 'cake',
                'match' => ['dashboard.menus'],
            ],
            [
                'label' => __('dashboard.nav.schedule'),
                'route' => 'dashboard.schedule',
                'icon' => 'clock',
                'match' => ['dashboard.schedule'],
            ],
            [
                'label' => __('dashboard.nav.photos'),
                'route' => 'dashboard.photos',
                'icon' => 'photo',
                'match' => ['dashboard.photos'],
            ],
            [
                'label' => __('dashboard.nav.guests'),
                'route' => 'dashboard.guests',
                'icon' => 'users',
                'match' => ['dashboard.guests', 'dashboard.guests.*'],
            ],
            [
                'label' => __('dashboard.nav.messages'),
                'route' => 'dashboard.messages',
                'icon' => 'message',
                'match' => ['dashboard.messages', 'dashboard.messages.photos', 'dashboard.messages.videos'],
            ],
            [
                'label' => __('dashboard.nav.budget'),
                'route' => 'dashboard.budget',
                'icon' => 'calculator',
                'match' => ['dashboard.budget'],
            ],
            [
                'label' => __('dashboard.nav.seating'),
                'route' => 'dashboard.seating',
                'icon' => 'table',
                'match' => ['dashboard.seating'],
            ],
            [
                'label' => __('dashboard.nav.pushes'),
                'route' => 'dashboard.pushes',
                'icon' => 'bell',
                'match' => ['dashboard.pushes', 'dashboard.pushes.*'],
            ],
        ];
    }

    /**
     * Primary mobile bottom-tab destinations.
     *
     * @return list<array{label: string, route: string, icon: string, match: list<string>}>
     */
    public static function tabItems(): array
    {
        return [
            [
                'label' => __('dashboard.nav.overview'),
                'route' => 'dashboard',
                'icon' => 'home',
                'match' => ['dashboard'],
            ],
            [
                'label' => __('dashboard.nav.guests'),
                'route' => 'dashboard.guests',
                'icon' => 'users',
                'match' => ['dashboard.guests', 'dashboard.guests.*'],
            ],
            [
                'label' => __('dashboard.nav.wedding'),
                'route' => 'dashboard.wedding',
                'icon' => 'heart',
                'match' => [
                    'dashboard.wedding',
                    'dashboard.wedding.details',
                    'dashboard.wedding.design',
                    'dashboard.locations',
                    'dashboard.menus',
                    'dashboard.schedule',
                    'dashboard.photos',
                    'dashboard.checklist',
                    'dashboard.budget',
                    'dashboard.seating',
                ],
            ],
            [
                'label' => __('dashboard.nav.messages'),
                'route' => 'dashboard.messages',
                'icon' => 'message',
                'match' => ['dashboard.messages', 'dashboard.messages.photos', 'dashboard.messages.videos'],
            ],
            [
                'label' => __('dashboard.nav.more'),
                'route' => 'dashboard.more',
                'icon' => 'more',
                'match' => [
                    'dashboard.more',
                    'dashboard.notifications',
                    'dashboard.pushes',
                    'dashboard.pushes.*',
                    'dashboard.pricing',
                    'dashboard.referrals',
                    'dashboard.partner',
                    'dashboard.profile',
                ],
            ],
        ];
    }

    /**
     * Wedding section sub-navigation (mobile hub + desktop convenience).
     *
     * @return list<array{label: string, route: string, icon: string, match: list<string>}>
     */
    public static function weddingSubItems(): array
    {
        return [
            [
                'label' => __('dashboard.nav.wedding'),
                'route' => 'dashboard.wedding',
                'icon' => 'heart',
                'match' => ['dashboard.wedding'],
            ],
            [
                'label' => __('dashboard.nav.locations'),
                'route' => 'dashboard.locations',
                'icon' => 'map',
                'match' => ['dashboard.locations'],
            ],
            [
                'label' => __('dashboard.nav.menus'),
                'route' => 'dashboard.menus',
                'icon' => 'cake',
                'match' => ['dashboard.menus'],
            ],
            [
                'label' => __('dashboard.nav.schedule'),
                'route' => 'dashboard.schedule',
                'icon' => 'clock',
                'match' => ['dashboard.schedule'],
            ],
            [
                'label' => __('dashboard.nav.photos'),
                'route' => 'dashboard.photos',
                'icon' => 'photo',
                'match' => ['dashboard.photos'],
            ],
        ];
    }

    /**
     * Overflow destinations shown on the mobile More screen.
     *
     * @return list<array{title: string, items: list<array{label: string, route: string, icon: string, match: list<string>, description?: string}>}>
     */
    public static function moreItemGroups(): array
    {
        $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;

        return [
            [
                'title' => __('dashboard.more_group_activity'),
                'items' => self::moreActivityItems($unreadCount),
            ],
            [
                'title' => __('dashboard.more_group_wedding'),
                'items' => self::moreWeddingItems(),
            ],
            [
                'title' => __('dashboard.more_group_account'),
                'items' => self::moreAccountItems(),
            ],
        ];
    }

    /**
     * @return list<array{label: string, route: string, icon: string, match: list<string>, description?: string, badge?: int}>
     */
    public static function moreActivityItems(int $unreadCount = 0): array
    {
        return [
            [
                'label' => __('dashboard.nav.notifications'),
                'route' => 'dashboard.notifications',
                'icon' => 'bell',
                'match' => ['dashboard.notifications'],
                'description' => $unreadCount > 0
                    ? __('dashboard.more_desc_notifications_unread', ['count' => $unreadCount])
                    : __('dashboard.more_desc_notifications'),
                'badge' => $unreadCount > 0 ? $unreadCount : null,
            ],
        ];
    }

    /**
     * Mobile wedding hub grouped destinations.
     *
     * @return list<array{title: string, items: list<array{label: string, route: string, icon: string, match: list<string>, description?: string}>}>
     */
    public static function weddingHubGroups(): array
    {
        return [
            [
                'title' => __('dashboard.more_group_wedding_setup'),
                'items' => [
                    [
                        'label' => __('dashboard.nav.wedding_details'),
                        'route' => 'dashboard.wedding.details',
                        'icon' => 'users',
                        'match' => ['dashboard.wedding.details'],
                        'description' => __('dashboard.more_desc_wedding_details'),
                    ],
                    [
                        'label' => __('dashboard.nav.wedding_design'),
                        'route' => 'dashboard.wedding.design',
                        'icon' => 'pencil',
                        'match' => ['dashboard.wedding.design'],
                        'description' => __('dashboard.more_desc_wedding_design'),
                    ],
                    [
                        'label' => __('dashboard.nav.locations'),
                        'route' => 'dashboard.locations',
                        'icon' => 'map',
                        'match' => ['dashboard.locations'],
                        'description' => __('dashboard.more_desc_locations'),
                    ],
                    [
                        'label' => __('dashboard.nav.menus'),
                        'route' => 'dashboard.menus',
                        'icon' => 'cake',
                        'match' => ['dashboard.menus'],
                        'description' => __('dashboard.more_desc_menus'),
                    ],
                    [
                        'label' => __('dashboard.nav.schedule'),
                        'route' => 'dashboard.schedule',
                        'icon' => 'clock',
                        'match' => ['dashboard.schedule'],
                        'description' => __('dashboard.more_desc_schedule'),
                    ],
                    [
                        'label' => __('dashboard.nav.photos'),
                        'route' => 'dashboard.photos',
                        'icon' => 'photo',
                        'match' => ['dashboard.photos'],
                        'description' => __('dashboard.more_desc_photos'),
                    ],
                ],
            ],
            [
                'title' => __('dashboard.more_group_planning'),
                'items' => [
                    [
                        'label' => __('dashboard.nav.seating'),
                        'route' => 'dashboard.seating',
                        'icon' => 'table',
                        'match' => ['dashboard.seating'],
                        'description' => __('dashboard.more_desc_seating'),
                    ],
                    [
                        'label' => __('dashboard.nav.checklist'),
                        'route' => 'dashboard.checklist',
                        'icon' => 'checklist',
                        'match' => ['dashboard.checklist'],
                        'description' => __('dashboard.more_desc_checklist'),
                    ],
                    [
                        'label' => __('dashboard.nav.budget'),
                        'route' => 'dashboard.budget',
                        'icon' => 'calculator',
                        'match' => ['dashboard.budget'],
                        'description' => __('dashboard.more_desc_budget'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return list<array{label: string, route: string, icon: string, match: list<string>, description?: string}>
     */
    public static function moreWeddingItems(): array
    {
        return [
            [
                'label' => __('dashboard.nav.partner'),
                'route' => 'dashboard.partner',
                'icon' => 'users',
                'match' => ['dashboard.partner'],
                'description' => __('dashboard.more_desc_partner'),
            ],
            [
                'label' => __('dashboard.nav.pushes'),
                'route' => 'dashboard.pushes',
                'icon' => 'bell',
                'match' => ['dashboard.pushes', 'dashboard.pushes.*'],
                'description' => __('dashboard.more_desc_pushes'),
            ],
        ];
    }

    /**
     * @return list<array{label: string, route: string, icon: string, match: list<string>, description?: string}>
     */
    public static function moreAccountItems(): array
    {
        return [
            [
                'label' => __('dashboard.nav.pricing'),
                'route' => 'dashboard.pricing',
                'icon' => 'credit-card',
                'match' => ['dashboard.pricing'],
                'description' => __('dashboard.more_desc_pricing'),
            ],
            [
                'label' => __('dashboard.nav.referrals'),
                'route' => 'dashboard.referrals',
                'icon' => 'gift',
                'match' => ['dashboard.referrals'],
                'description' => __('dashboard.more_desc_referrals'),
            ],
            [
                'label' => __('dashboard.nav.profile'),
                'route' => 'dashboard.profile',
                'icon' => 'user',
                'match' => ['dashboard.profile'],
                'description' => __('dashboard.more_desc_profile'),
            ],
        ];
    }

    /**
     * @return list<array{label: string, route: string, icon: string, match: list<string>, description?: string}>
     */
    public static function moreItems(): array
    {
        $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;

        return array_merge(
            self::moreActivityItems($unreadCount),
            self::moreWeddingItems(),
            self::moreAccountItems(),
        );
    }

    public static function unreadNotificationCount(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    public static function isMobileRootTab(): bool
    {
        return request()->routeIs([
            'dashboard',
            'dashboard.guests',
            'dashboard.wedding',
            'dashboard.messages',
            'dashboard.more',
        ]);
    }

    public static function usesMobileHeader(): bool
    {
        return ! self::isMobileRootTab();
    }

    /**
     * @return list<array{label: string, route: string, icon: string, match: list<string>}>
     */
    public static function footerItems(): array
    {
        return [
            [
                'label' => __('dashboard.nav.pricing'),
                'route' => 'dashboard.pricing',
                'icon' => 'credit-card',
                'match' => ['dashboard.pricing'],
            ],
            [
                'label' => __('dashboard.nav.referrals'),
                'route' => 'dashboard.referrals',
                'icon' => 'gift',
                'match' => ['dashboard.referrals'],
            ],
            [
                'label' => __('dashboard.nav.partner'),
                'route' => 'dashboard.partner',
                'icon' => 'users',
                'match' => ['dashboard.partner'],
            ],
            [
                'label' => __('dashboard.nav.profile'),
                'route' => 'dashboard.profile',
                'icon' => 'user',
                'match' => ['dashboard.profile'],
            ],
        ];
    }

    public static function isActive(array $item): bool
    {
        foreach ($item['match'] as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }

    public static function isWeddingSection(): bool
    {
        return request()->routeIs([
            'dashboard.wedding',
            'dashboard.wedding.details',
            'dashboard.wedding.design',
            'dashboard.locations',
            'dashboard.menus',
            'dashboard.schedule',
            'dashboard.photos',
        ]);
    }

    public static function homeUrl(): string
    {
        return route('dashboard');
    }

    public static function pricingUrl(): string
    {
        return route('dashboard.pricing');
    }
}

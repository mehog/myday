<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

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
                    'dashboard.locations',
                    'dashboard.menus',
                    'dashboard.schedule',
                    'dashboard.photos',
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
                    'dashboard.checklist',
                    'dashboard.budget',
                    'dashboard.seating',
                    'dashboard.pushes',
                    'dashboard.pushes.*',
                    'dashboard.pricing',
                    'dashboard.referrals',
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
     * @return list<array{label: string, route: string, icon: string, match: list<string>, description?: string}>
     */
    public static function moreItems(): array
    {
        return [
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
            [
                'label' => __('dashboard.nav.seating'),
                'route' => 'dashboard.seating',
                'icon' => 'table',
                'match' => ['dashboard.seating'],
                'description' => __('dashboard.more_desc_seating'),
            ],
            [
                'label' => __('dashboard.nav.pushes'),
                'route' => 'dashboard.pushes',
                'icon' => 'bell',
                'match' => ['dashboard.pushes', 'dashboard.pushes.*'],
                'description' => __('dashboard.more_desc_pushes'),
            ],
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
            'dashboard.locations',
            'dashboard.menus',
            'dashboard.schedule',
            'dashboard.photos',
        ]);
    }

    public static function homeUrl(): string
    {
        if (config('dashboard.default') && Route::has('dashboard')) {
            return route('dashboard');
        }

        return url('/app');
    }

    public static function pricingUrl(): string
    {
        if (config('dashboard.default') && Route::has('dashboard.pricing')) {
            return route('dashboard.pricing');
        }

        return url('/app/pricing');
    }
}

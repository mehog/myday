<?php

namespace App\Support;

use App\WeddingTaskPeriod;
use App\WeddingTaskPriority;

class WeddingTaskCatalog
{
    /**
     * @return list<array{
     *     key: string,
     *     period: WeddingTaskPeriod,
     *     priority: WeddingTaskPriority,
     *     due_offset_days: int,
     *     sort_order: int,
     *     progress: ?string,
     *     action_route: ?string
     * }>
     */
    public static function all(): array
    {
        return [
            self::task('set_budget', WeddingTaskPeriod::NineToTwelveMonths, 330, 10, actionRoute: 'dashboard.budget'),
            self::task('add_guests', WeddingTaskPeriod::NineToTwelveMonths, 315, 20, progress: 'add_guests', actionRoute: 'dashboard.guests'),
            self::task('book_venue', WeddingTaskPeriod::NineToTwelveMonths, 300, 30),
            self::task('book_photographer', WeddingTaskPeriod::NineToTwelveMonths, 285, 40),
            self::task('book_music', WeddingTaskPeriod::NineToTwelveMonths, 270, 50),
            self::task('set_location', WeddingTaskPeriod::NineToTwelveMonths, 255, 60, progress: 'set_location', actionRoute: 'dashboard.locations'),

            self::task('book_attire', WeddingTaskPeriod::SixToNineMonths, 240, 70),
            self::task('book_rings', WeddingTaskPeriod::SixToNineMonths, 225, 80),
            self::task('book_decor', WeddingTaskPeriod::SixToNineMonths, 210, 90),
            self::task('plan_invitations', WeddingTaskPeriod::SixToNineMonths, 200, 100, actionRoute: 'dashboard.wedding'),
            self::task('organize_accommodation', WeddingTaskPeriod::SixToNineMonths, 190, 110),
            self::task('book_transport', WeddingTaskPeriod::SixToNineMonths, 180, 120),
            self::task('add_photos', WeddingTaskPeriod::SixToNineMonths, 170, 130, progress: 'add_photos', actionRoute: 'dashboard.photos'),

            self::task('set_schedule', WeddingTaskPeriod::ThreeToSixMonths, 150, 140, progress: 'set_schedule', actionRoute: 'dashboard.schedule'),
            self::task('plan_menu', WeddingTaskPeriod::ThreeToSixMonths, 140, 150, actionRoute: 'dashboard.menus'),
            self::task('gather_documents', WeddingTaskPeriod::ThreeToSixMonths, 130, 160),
            self::task('book_cake', WeddingTaskPeriod::ThreeToSixMonths, 120, 170),
            self::task('plan_ceremony', WeddingTaskPeriod::ThreeToSixMonths, 110, 180),

            self::task('send_invitations', WeddingTaskPeriod::OneToThreeMonths, 75, 190, WeddingTaskPriority::High, 'send_invitations', 'dashboard.guests'),
            self::task('track_rsvp', WeddingTaskPeriod::OneToThreeMonths, 60, 200, progress: 'track_rsvp', actionRoute: 'dashboard.guests'),
            self::task('finish_seating', WeddingTaskPeriod::OneToThreeMonths, 45, 210, WeddingTaskPriority::High, 'finish_seating', 'dashboard.seating'),
            self::task('confirm_menu', WeddingTaskPeriod::OneToThreeMonths, 30, 220, progress: 'confirm_menu', actionRoute: 'dashboard.guests'),

            self::task('confirm_guest_count', WeddingTaskPeriod::TwoToFourWeeks, 21, 230, actionRoute: 'dashboard.guests'),
            self::task('review_budget', WeddingTaskPeriod::TwoToFourWeeks, 18, 240, progress: 'review_budget', actionRoute: 'dashboard.budget'),
            self::task('send_guest_updates', WeddingTaskPeriod::TwoToFourWeeks, 14, 250, actionRoute: 'dashboard.pushes'),
            self::task('finalize_details', WeddingTaskPeriod::TwoToFourWeeks, 10, 260),

            self::task('confirm_vendors', WeddingTaskPeriod::LastWeek, 7, 270),
            self::task('collect_items', WeddingTaskPeriod::LastWeek, 5, 280),
            self::task('confirm_final_counts', WeddingTaskPeriod::LastWeek, 3, 290, actionRoute: 'dashboard.guests'),
            self::task('last_week_wrapup', WeddingTaskPeriod::LastWeek, 1, 300),
        ];
    }

    /**
     * @return array{
     *     key: string,
     *     period: WeddingTaskPeriod,
     *     priority: WeddingTaskPriority,
     *     due_offset_days: int,
     *     sort_order: int,
     *     progress: ?string,
     *     action_route: ?string
     * }|null
     */
    public static function definition(string $key): ?array
    {
        foreach (self::all() as $definition) {
            if ($definition['key'] === $key) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_column(self::all(), 'key');
    }

    /**
     * @return array{
     *     key: string,
     *     period: WeddingTaskPeriod,
     *     priority: WeddingTaskPriority,
     *     due_offset_days: int,
     *     sort_order: int,
     *     progress: ?string,
     *     action_route: ?string
     * }
     */
    protected static function task(
        string $key,
        WeddingTaskPeriod $period,
        int $dueOffsetDays,
        int $sortOrder,
        WeddingTaskPriority $priority = WeddingTaskPriority::Normal,
        ?string $progress = null,
        ?string $actionRoute = null,
    ): array {
        return [
            'key' => $key,
            'period' => $period,
            'priority' => $priority,
            'due_offset_days' => $dueOffsetDays,
            'sort_order' => $sortOrder,
            'progress' => $progress,
            'action_route' => $actionRoute,
        ];
    }
}

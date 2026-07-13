<?php

namespace App\Support;

use App\Models\Enquiry;
use App\Models\ReferralPayout;
use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Database\Eloquent\Builder;

class AdminDashboardMetrics
{
    public static function pendingActivationsCount(): int
    {
        return self::pendingActivationsQuery()->count();
    }

    /**
     * @return Builder<WeddingEvent>
     */
    public static function pendingActivationsQuery(): Builder
    {
        return WeddingEvent::query()
            ->with('user')
            ->where('is_active', false)
            ->where('is_demo', false)
            ->orderBy('wedding_date');
    }

    public static function unverifiedCouplesCount(): int
    {
        return self::unverifiedUsersQuery()->count();
    }

    /**
     * @return Builder<User>
     */
    public static function unverifiedUsersQuery(): Builder
    {
        return User::query()
            ->whereNull('email_verified_at')
            ->where('is_admin', false)
            ->orderByDesc('created_at');
    }

    public static function newSignupsCount(): int
    {
        return User::query()
            ->where('is_admin', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }

    public static function pendingPayoutsCount(): int
    {
        return self::pendingPayoutsQuery()->count();
    }

    /**
     * @return Builder<ReferralPayout>
     */
    public static function pendingPayoutsQuery(): Builder
    {
        return ReferralPayout::query()
            ->with('referrer')
            ->pending()
            ->orderByDesc('created_at');
    }

    public static function recentEnquiriesCount(): int
    {
        return Enquiry::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }

    /**
     * @return Builder<Enquiry>
     */
    public static function recentEnquiriesQuery(): Builder
    {
        return Enquiry::query()
            ->orderByDesc('created_at');
    }
}

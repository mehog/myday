<?php

namespace App\Support;

use App\Models\ReferralPayout;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog;

class AdminDashboardMetrics
{
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
            ->with('weddingEvent')
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

    public static function successfulLoginsTodayCount(): int
    {
        return AuthenticationLog::query()
            ->successful()
            ->where('login_at', '>=', now()->startOfDay())
            ->count();
    }

    public static function failedLoginsTodayCount(): int
    {
        return AuthenticationLog::query()
            ->failed()
            ->where('login_at', '>=', now()->startOfDay())
            ->count();
    }

    /**
     * @return Builder<AuthenticationLog>
     */
    public static function recentSuccessfulLoginsQuery(int $limit = 5): Builder
    {
        return AuthenticationLog::query()
            ->with('authenticatable')
            ->successful()
            ->orderByDesc('login_at')
            ->limit($limit);
    }
}

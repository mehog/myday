<?php

namespace App\Support;

use App\Helpers\IpStackCacheHelper;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserSignupIp
{
    public static function capture(User $user, ?string $ip = null): void
    {
        if (config('app.env') !== 'production') {
            return;
        }

        if ($user->signup_ipstack && isset($user->signup_ipstack->ip)) {
            Log::info('UserSignupIp: User already has IPStack data, skipping', [
                'user_id' => $user->id,
                'ip' => $user->signup_ipstack->ip,
            ]);

            return;
        }

        try {
            $ip ??= request()->ip();
            $ipstack = IpStackCacheHelper::getOrFetch($ip);

            if ($ipstack && isset($ipstack->ip)) {
                $user->update([
                    'signup_ipstack' => $ipstack,
                    'signup_ip' => $ip,
                ]);

                Log::info('UserSignupIp: Applied IPStack data to user', [
                    'user_id' => $user->id,
                    'country_code' => $ipstack->country_code ?? 'unknown',
                ]);

                return;
            }

            $user->update(['signup_ip' => $ip]);

            Log::warning('UserSignupIp: No IPStack data available, saved IP only', [
                'user_id' => $user->id,
                'ip' => $ip,
            ]);
        } catch (\Throwable $th) {
            Log::error('UserSignupIp failed: '.$th->getMessage(), [
                'user_id' => $user->id ?? 'unknown',
            ]);
        }
    }
}

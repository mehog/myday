<?php

namespace App\Listeners;

use App\Helpers\IpStackCacheHelper;
use Illuminate\Auth\Events\Verified as UserVerified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackUserIp
{
    public function __construct(protected Request $request) {}

    public function handle(UserVerified $event): void
    {
        if (config('app.env') !== 'production') {
            return;
        }

        if ($event->user->signup_ipstack && isset($event->user->signup_ipstack->ip)) {
            Log::info('✅ TrackUserIp: User already has IPStack data, skipping', [
                'user_id' => $event->user->id,
                'ip' => $event->user->signup_ipstack->ip,
            ]);

            return;
        }

        try {
            $ip = $this->request->ip();
            $ipstack = IpStackCacheHelper::getOrFetch($ip);

            if ($ipstack && isset($ipstack->ip)) {
                $event->user->update([
                    'signup_ipstack' => $ipstack,
                    'signup_ip' => $ip,
                ]);

                Log::info('✅ TrackUserIp: Applied IPStack data to user', [
                    'user_id' => $event->user->id,
                    'country_code' => $ipstack->country_code ?? 'unknown',
                ]);
            } else {
                $event->user->update(['signup_ip' => $ip]);

                Log::warning('⚠️ TrackUserIp: No IPStack data available, saved IP only', [
                    'user_id' => $event->user->id,
                    'ip' => $ip,
                ]);
            }
        } catch (\Throwable $th) {
            Log::error('❌ TrackUserIp failed: '.$th->getMessage(), [
                'user_id' => $event->user->id ?? 'unknown',
            ]);
        }
    }
}

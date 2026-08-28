<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerifiedOrGrace
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if (! $user instanceof MustVerifyEmail) {
            return $next($request);
        }

        if ($user->hasVerifiedEmail() || $user->hasEmailVerificationGrace()) {
            return $next($request);
        }

        return $request->expectsJson()
            ? abort(403, 'Your email address is not verified.')
            : redirect()->route('verification.notice');
    }
}

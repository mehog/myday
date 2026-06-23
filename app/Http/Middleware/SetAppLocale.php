<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetAppLocale
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', 'bs');

        if (! in_array($locale, ['en', 'bs'], true)) {
            $locale = 'bs';
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}

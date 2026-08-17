<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacyDomains
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $canonicalHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        if ($canonicalHost !== '' && $host === $canonicalHost) {
            return $next($request);
        }

        /** @var list<string> $legacyHosts */
        $legacyHosts = config('app.legacy_redirect_hosts', []);

        if (! in_array($host, $legacyHosts, true)) {
            return $next($request);
        }

        $targetBase = rtrim((string) config('app.url'), '/');

        return redirect()->to($targetBase.$request->getRequestUri(), 301);
    }
}

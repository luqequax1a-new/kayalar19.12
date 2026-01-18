<?php

namespace FleetCart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackPageView
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            // Performance FIX: Disable tracking on local to prevent 14s LCP treshold
            if (app()->environment('local') || in_array(request()->getHost(), ['127.0.0.1', 'localhost'])) {
                return $response;
            }

            if (!$request->isMethod('get')) {
                return $response;
            }

            if ($request->expectsJson()) {
                return $response;
            }

            $path = '/' . ltrim($request->path(), '/');

            // Ignore admin, api and static assets
            if ($request->is('admin') || $request->is('admin/*') || $request->is('api') || $request->is('api/*')) {
                return $response;
            }

            if ($request->is('storage/*') || $request->is('build/*') || $request->is('vendor/*')) {
                return $response;
            }

            // Only track HTML page views
            $accept = (string) $request->header('accept');
            if ($accept !== '' && stripos($accept, 'text/html') === false) {
                return $response;
            }

            $sessionId = (string) optional($request->session())->getId();
            $ip = (string) $request->ip();
            $ua = (string) $request->userAgent();

            $fingerprint = $sessionId !== '' ? $sessionId : ($ip !== '' ? $ip : 'anon');
            $fingerprintHash = hash('sha256', $fingerprint);

            // Unique daily visit per fingerprint (cached for performance)
            $today = now()->toDateString();
            $cacheKey = 'page_view_tracked_' . $fingerprintHash . '_' . $today;
            
            // Check cache first to avoid database query
            $already = cache()->remember($cacheKey, 86400, function () use ($fingerprintHash, $today) {
                return DB::table('page_views')
                    ->where('fingerprint', $fingerprintHash)
                    ->whereDate('created_at', $today)
                    ->exists();
            });

            if ($already) {
                return $response;
            }

            DB::table('page_views')->insert([
                'session_id' => $sessionId !== '' ? $sessionId : null,
                'fingerprint' => $fingerprintHash,
                'path' => $path,
                'referrer' => (string) $request->header('referer') ?: null,
                'ip' => $ip !== '' ? $ip : null,
                'user_agent' => $ua !== '' ? mb_substr($ua, 0, 512) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
        }

        return $response;
    }
}

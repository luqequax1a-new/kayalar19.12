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

            // Unique daily visit per fingerprint (DB-based, so truncation resets immediately)
            $today = now()->toDateString();
            $already = DB::table('page_views')
                ->where('fingerprint', $fingerprintHash)
                ->whereDate('created_at', $today)
                ->exists();

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

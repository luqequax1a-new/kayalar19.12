<?php

namespace Modules\Product\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListingInstrumentation
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->shouldInstrument($request)) {
            return $next($request);
        }

        $start = microtime(true);
        $queryCount = 0;
        $dbTimeMs = 0.0;

        DB::listen(function ($query) use (&$queryCount, &$dbTimeMs) {
            $queryCount++;
            $dbTimeMs += (float) $query->time;
        });

        $response = $next($request);

        $totalTimeMs = (microtime(true) - $start) * 1000;
        $memMb = memory_get_peak_usage(true) / 1024 / 1024;

        $bytes = null;
        try {
            if (method_exists($response, 'getContent')) {
                $content = $response->getContent();
                if (is_string($content)) {
                    $bytes = strlen($content);
                }
            }
        } catch (\Throwable $e) {
            $bytes = null;
        }

        try {
            if (isset($response->headers)) {
                $response->headers->set('X-Listing-Time', (string) round($totalTimeMs, 2));
                $response->headers->set('X-Listing-Queries', (string) $queryCount);
                $response->headers->set('X-Listing-DBTime', (string) round($dbTimeMs, 2));
                $response->headers->set('X-Listing-Mem', (string) round($memMb, 2));
            }
        } catch (\Throwable $e) {
            // Ignore header failures in dev instrumentation.
        }

        $sanitized = $this->sanitizeQueryParams($request->query());

        Log::info(sprintf(
            'listing_instr method=%s path=%s params=%s total_ms=%.2f q=%d db_ms=%.2f bytes=%s',
            $request->method(),
            $request->getPathInfo(),
            json_encode($sanitized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $totalTimeMs,
            $queryCount,
            $dbTimeMs,
            $bytes === null ? 'n/a' : (string) $bytes
        ));

        return $response;
    }

    private function shouldInstrument(Request $request): bool
    {
        if (app()->environment('production')) {
            return false;
        }

        if (! (app()->environment(['local', 'development', 'dev', 'testing']) || (bool) config('app.debug'))) {
            return false;
        }

        if (! ($request->expectsJson() || $request->wantsJson() || $request->ajax())) {
            return false;
        }

        return true;
    }

    private function sanitizeQueryParams(array $params): array
    {
        $redactKeys = ['password', 'token', 'api_token', 'access_token', 'refresh_token', 'authorization'];

        foreach ($params as $key => $value) {
            if (in_array((string) $key, $redactKeys, true)) {
                $params[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $params[$key] = $this->sanitizeQueryParams($value);
                continue;
            }

            if (is_string($value) && mb_strlen($value) > 200) {
                $params[$key] = mb_substr($value, 0, 200) . '…';
            }
        }

        return $params;
    }
}

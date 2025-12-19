<?php

namespace FleetCart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CaptureOrderAttribution
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            if (! $request->hasSession()) {
                return $response;
            }

            if (! in_array(strtoupper($request->method()), ['GET', 'HEAD'], true)) {
                return $response;
            }

            $path = ltrim((string) $request->path(), '/');

            if ($path === '' || $path === '/') {
                // ok
            }

            if (str_starts_with($path, 'admin') || str_starts_with($path, 'api')) {
                return $response;
            }

            if ($request->ajax()) {
                return $response;
            }

            $incoming = [
                'utm_source' => $this->cleanString($request->query('utm_source')),
                'utm_medium' => $this->cleanString($request->query('utm_medium')),
                'utm_campaign' => $this->cleanString($request->query('utm_campaign')),
                'utm_term' => $this->cleanString($request->query('utm_term')),
                'utm_content' => $this->cleanString($request->query('utm_content')),
                'click_id_gclid' => $this->cleanString($request->query('gclid')),
                'click_id_fbclid' => $this->cleanString($request->query('fbclid')),
            ];

            $hasMarketingParams = collect($incoming)->filter(function ($v) {
                return $v !== null && $v !== '';
            })->isNotEmpty();

            $sessionKey = 'order_attribution';
            $current = (array) $request->session()->get($sessionKey, []);

            if (empty($current)) {
                $current = [
                    'landing_url' => $this->cleanString($request->getRequestUri()),
                    'referrer_url' => $this->cleanString($request->headers->get('referer')),
                ];

                foreach ($incoming as $k => $v) {
                    if ($v !== null && $v !== '') {
                        $current[$k] = $v;
                    }
                }

                $request->session()->put($sessionKey, $current);

                return $response;
            }

            if ($hasMarketingParams) {
                foreach ($incoming as $k => $v) {
                    if ($v !== null && $v !== '') {
                        $current[$k] = $v;
                    }
                }

                if (empty($current['referrer_url'])) {
                    $current['referrer_url'] = $this->cleanString($request->headers->get('referer'));
                }

                $request->session()->put($sessionKey, $current);
            }
        } catch (\Throwable $e) {
        }

        return $response;
    }

    private function cleanString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }
}

<?php

declare(strict_types=1);

namespace FleetCart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ResponseCacheStorefront
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethod('GET')) {
            $response = $next($request);
            $response->headers->set('X-Page-Cache', 'BYPASS');
            $response->headers->set('X-Page-Cache-Reason', 'non_get');
            return $response;
        }

        if ($request->is('admin*') || $request->is('account*') || $request->is('checkout*') || $request->is('cart*')) {
            $response = $next($request);
            $response->headers->set('X-Page-Cache', 'BYPASS');
            $response->headers->set('X-Page-Cache-Reason', 'excluded_path');
            return $response;
        }

        if (auth()->check()) {
            $response = $next($request);
            $response->headers->set('X-Page-Cache', 'BYPASS');
            $response->headers->set('X-Page-Cache-Reason', 'authenticated');
            return $response;
        }

        $isListing =
            $request->is('products') ||
            $request->is('categories/*/products') ||
            $request->is('brands/*/products') ||
            $request->is('tags/*/products');

        if (! $isListing) {
            $response = $next($request);
            $response->headers->set('X-Page-Cache', 'BYPASS');
            $response->headers->set('X-Page-Cache-Reason', 'not_listing');
            return $response;
        }

        $key = 'pagecache:' . sha1($request->fullUrl() . '|' . app()->getLocale());

        $ttlSeconds = (int) config('app.storefront_page_cache_ttl', 60);

        try {
            if ($html = Cache::get($key)) {
                return response($html, 200)
                    ->header('Content-Type', 'text/html; charset=UTF-8')
                    ->header('X-Page-Cache', 'HIT')
                    ->header('Cache-Control', 'public, max-age=30, stale-while-revalidate=60');
            }
        } catch (\Throwable $e) {
            // Cache store misconfig/connection/table issues should never break storefront.
            $response = $next($request);
            $response->headers->set('X-Page-Cache', 'BYPASS');
            $response->headers->set('X-Page-Cache-Reason', 'cache_read_error');
            return $response;
        }

        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type', '');

        if ($response->isSuccessful() && str_contains($contentType, 'text/html')) {
            try {
                Cache::put($key, $response->getContent(), $ttlSeconds);
                $response->headers->set('X-Page-Cache', 'MISS');
                $response->headers->set('Cache-Control', 'public, max-age=30, stale-while-revalidate=60');
            } catch (\Throwable $e) {
                // Ignore cache write failures.
                $response->headers->set('X-Page-Cache', 'BYPASS');
                $response->headers->set('X-Page-Cache-Reason', 'cache_write_error');
            }
        }

        return $response;
    }
}

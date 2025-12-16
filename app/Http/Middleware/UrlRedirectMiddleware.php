<?php

namespace FleetCart\Http\Middleware;

use Closure;
use Modules\Product\Entities\UrlRedirect;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UrlRedirectMiddleware
{
    public function handle($request, Closure $next)
    {
        $path = $request->getPathInfo();

        if (str_starts_with($path, '/admin')) {
            return $next($request);
        }

        $canonical = $this->canonicalPath($path);

        if (str_starts_with($canonical, '/admin')) {
            return $next($request);
        }

        $attrKey = 'url_redirect.lookup:' . $canonical;
        if ($request->attributes->has($attrKey)) {
            $redirect = $request->attributes->get($attrKey);
        } else {
            $redirect = UrlRedirect::query()
                ->where('is_active', true)
                ->where('source_path', $canonical)
                ->first();

            $request->attributes->set($attrKey, $redirect);
        }

        if ($redirect && $redirect->target_url) {
            if ($redirect->target_url === $path) {
                return $next($request);
            }

            $status = in_array($redirect->status_code, [301, 302]) ? $redirect->status_code : 301;
            $target = $redirect->target_url ?: '/';

            return redirect($target, $status);
        }

        return $next($request);
    }

    protected function canonicalPath(string $path): string
    {
        $segments = explode('/', ltrim($path, '/'));
        $supportedLocales = array_keys(LaravelLocalization::getSupportedLocales());

        if (!empty($segments) && in_array($segments[0], $supportedLocales)) {
            array_shift($segments);
        }

        if (empty($segments)) {
            return '/';
        }

        return '/' . implode('/', $segments);
    }
}

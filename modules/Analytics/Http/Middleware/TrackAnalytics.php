<?php

namespace Modules\Analytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Analytics\Services\AnalyticsService;

class TrackAnalytics
{
    protected $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Track page view for non-AJAX requests
        if (!$request->ajax() && $request->method() === 'GET') {
            $this->trackPageView($request);
        }

        return $response;
    }

    /**
     * Track page view
     */
    protected function trackPageView(Request $request): void
    {
        try {
            $data = [
                'page_location' => $request->fullUrl(),
                'page_title' => $request->route()?->getName() ?? 'Unknown',
            ];

            $this->analytics->trackEvent('page_view', $data);
        } catch (\Exception $e) {
            // Silent fail
        }
    }
}

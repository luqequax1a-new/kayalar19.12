<?php

namespace Modules\Support\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Modules\Support\Services\SitemapService;

class SitemapCronController
{
    public function run(string $token, SitemapService $sitemapService)
    {
        $configuredToken = (string) setting('support.sitemap.cron_token', '');

        if ($configuredToken === '' || !hash_equals($configuredToken, (string) $token)) {
            Log::warning('Sitemap cron forbidden (token mismatch)', [
                'env' => (string) config('app.env'),
                'app_url' => (string) config('app.url'),
            ]);
            abort(403);
        }

        Log::info('Sitemap cron started', [
            'env' => (string) config('app.env'),
            'app_url' => (string) config('app.url'),
        ]);

        $sitemapService->generate();

        Log::info('Sitemap cron finished', [
            'env' => (string) config('app.env'),
            'app_url' => (string) config('app.url'),
        ]);

        return new Response('Sitemap generated', 200);
    }
}

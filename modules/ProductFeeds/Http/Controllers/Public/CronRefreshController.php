<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\Response;
use Modules\ProductFeeds\Services\FeedCacheService;

class CronRefreshController
{
    public function __construct(private readonly FeedCacheService $cache)
    {
    }

    public function handle(string $channel): Response
    {
        $validChannels = ['google', 'meta'];

        if (! in_array($channel, $validChannels, true)) {
            abort(404);
        }

        $token = (string) request()->query('token', '');
        $expected = (string) setting('product_feeds.cache.token', '');

        if ($expected === '' || ! hash_equals($expected, $token)) {
            abort(403);
        }

        // Force regeneration regardless of current cache state
        switch ($channel) {
            case 'google':
                $controller = app(GoogleFeedController::class);
                $controller->regenerateCache();
                break;
            case 'meta':
                $controller = app(MetaFeedController::class);
                $controller->regenerateCache();
                break;
        }

        return new Response('OK', 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}

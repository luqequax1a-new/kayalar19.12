<?php

namespace Modules\ProductFeeds\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\ProductFeeds\Http\Controllers\Public\GoogleFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\MetaFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\TrendyolFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\HepsiburadaFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\PinterestFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\TikTokFeedController;
use Modules\ProductFeeds\Services\FeedCacheService;

class FeedCacheController extends Controller
{
    public function __construct(private readonly FeedCacheService $cache)
    {
    }

    public function refresh(string $channel): RedirectResponse
    {
        $validChannels = ['google', 'meta', 'trendyol', 'hepsiburada', 'pinterest', 'tiktok'];

        if (! in_array($channel, $validChannels, true)) {
            abort(404);
        }

        try {
            switch ($channel) {
                case 'google':
                    app(GoogleFeedController::class)->regenerateCache();
                    break;
                case 'meta':
                    app(MetaFeedController::class)->regenerateCache();
                    break;
                case 'trendyol':
                    app(TrendyolFeedController::class)->regenerateCache();
                    break;
                case 'hepsiburada':
                    app(HepsiburadaFeedController::class)->regenerateCache();
                    break;
                case 'pinterest':
                    app(PinterestFeedController::class)->regenerateCache();
                    break;
                case 'tiktok':
                    app(TikTokFeedController::class)->regenerateCache();
                    break;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', trans('product_feeds::messages.cache_refreshed'));
    }

    public function regenerateToken(): RedirectResponse
    {
        $token = Str::random(32);

        setting(['product_feeds.cache.token' => $token]);

        return redirect()->back()->with('success', 'Cron token regenerated.');
    }
}

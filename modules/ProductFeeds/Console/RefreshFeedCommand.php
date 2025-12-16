<?php

namespace Modules\ProductFeeds\Console;

use Illuminate\Console\Command;
use Modules\ProductFeeds\Http\Controllers\Public\GoogleFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\MetaFeedController;

class RefreshFeedCommand extends Command
{
    protected $signature = 'feeds:refresh {channel : google|meta}';

    protected $description = 'Regenerate and cache a product feed for the given channel.';

    public function handle(): int
    {
        $channel = (string) $this->argument('channel');

        $validChannels = ['google', 'meta'];

        if (! in_array($channel, $validChannels, true)) {
            $this->error('Invalid channel. Allowed: ' . implode(', ', $validChannels));

            return 1;
        }

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

        $this->info('Feed cache refreshed for channel: ' . $channel);

        return 0;
    }
}

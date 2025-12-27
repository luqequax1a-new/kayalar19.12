<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncatePageViews extends Command
{
    protected $signature = 'page-views:truncate';

    protected $description = 'Truncate page_views table (reset visit metrics).';

    public function handle(): int
    {
        if (!Schema::hasTable('page_views')) {
            $this->error("Table 'page_views' not found.");
            return self::FAILURE;
        }

        DB::table('page_views')->truncate();

        $this->info('page_views table truncated.');

        return self::SUCCESS;
    }
}

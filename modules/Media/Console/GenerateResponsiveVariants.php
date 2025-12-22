<?php

namespace Modules\Media\Console;

use Illuminate\Console\Command;
use Modules\Media\Entities\File as MediaFile;
use Modules\Media\Jobs\GenerateResponsiveImagesForMedia;

class GenerateResponsiveVariants extends Command
{
    protected $signature = 'media:generate-variants {--chunk=500} {--disk=} {--queue} {--force} {--from-id=} {--to-id=} {--only-id=} {--skip-id=*} {--quiet-progress}';
    protected $description = 'Generate grid/card/thumb responsive image variants (JPEG/WebP/AVIF) for existing media images.';

    public function handle(): int
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '4096M');
        $chunk = (int) ($this->option('chunk') ?: 500);
        $disk = $this->option('disk');
        $useQueue = (bool) $this->option('queue');
        $force = (bool) $this->option('force');
        $fromId = $this->option('from-id');
        $toId = $this->option('to-id');
        $onlyId = $this->option('only-id');
        $skipIds = (array) $this->option('skip-id');
        $quietProgress = (bool) $this->option('quiet-progress');

        $query = MediaFile::query()
            ->where('mime', 'LIKE', 'image/%');

        if (!empty($onlyId)) {
            $query->where('id', (int) $onlyId);
        } else {
            if (!empty($fromId)) {
                $query->where('id', '>=', (int) $fromId);
            }
            if (!empty($toId)) {
                $query->where('id', '<=', (int) $toId);
            }
        }

        if (!empty($disk)) {
            $query->where('disk', $disk);
        }

        $count = 0;

        $query->orderBy('id')->chunk($chunk, function ($files) use (&$count, $useQueue, $force, $skipIds, $quietProgress) {
            foreach ($files as $file) {
                if (!empty($skipIds) && in_array((string) $file->id, array_map('strval', $skipIds), true)) {
                    continue;
                }
                $count++;
                if ($useQueue) {
                    dispatch(new GenerateResponsiveImagesForMedia($file->id, $force));
                } else {
                    // Run inline using the job’s handle
                    try {
                        if (!$quietProgress) {
                            $this->line("Processing media ID {$file->id} ({$file->disk}): {$file->getRawOriginal('path')}");
                        }
                        (new GenerateResponsiveImagesForMedia($file->id, $force))->handle(app(\Modules\Media\Services\ResponsiveImageGenerator::class));
                    } catch (\Throwable $e) {
                        $this->error("Failed for media ID {$file->id}: " . $e->getMessage());
                    }
                }
            }
        });

        $this->info("Processed {$count} images.");
        return 0;
    }
}

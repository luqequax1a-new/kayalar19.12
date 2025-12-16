<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Media\Entities\File as MediaFile;

class NormalizeMediaPathsCommand extends Command
{
    protected $signature = 'media:normalize-paths
        {--apply : Persist changes to the database}
        {--chunk=500 : Chunk size for processing}
        {--limit=0 : Limit number of records processed (0 = no limit)}
        {--disk= : Override disk to check for file existence (defaults to record disk)}';

    protected $description = 'Normalize media file paths in the files table to the canonical "media/<filename>" format.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $limit = (int) $this->option('limit');
        $overrideDisk = $this->option('disk');
        $overrideDisk = is_string($overrideDisk) && $overrideDisk !== '' ? $overrideDisk : null;

        $this->line($apply ? 'Mode: APPLY' : 'Mode: DRY-RUN');

        $baseQuery = MediaFile::query()->orderBy('id');
        if ($limit > 0) {
            $baseQuery->limit($limit);
        }

        $total = (clone $baseQuery)->count();
        $this->info("Scanning {$total} record(s)...");

        $changed = 0;
        $skipped = 0;
        $unchanged = 0;
        $samples = [];

        $processChunk = function ($rows) use (&$changed, &$skipped, &$unchanged, &$samples, $apply, $overrideDisk) {
            foreach ($rows as $row) {
                $old = (string) $row->getRawOriginal('path');
                $disk = $overrideDisk ?: ($row->disk ?: config('filesystems.default'));

                $new = $this->normalizePath($old, $disk);

                if ($new === null) {
                    $skipped++;
                    continue;
                }

                if ($new === $old) {
                    $unchanged++;
                    continue;
                }

                $changed++;

                if (count($samples) < 25) {
                    $samples[] = [
                        'id' => $row->id,
                        'disk' => $disk,
                        'old' => $old,
                        'new' => $new,
                    ];
                }

                if ($apply) {
                    MediaFile::query()->whereKey($row->id)->update(['path' => $new]);
                }
            }
        };

        $runner = function () use ($baseQuery, $chunkSize, $processChunk) {
            $baseQuery->chunkById($chunkSize, function ($rows) use ($processChunk) {
                $processChunk($rows);
            });
        };

        if ($apply) {
            DB::transaction(function () use ($runner) {
                $runner();
            });
        } else {
            $runner();
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line('  changed:   ' . $changed);
        $this->line('  unchanged: ' . $unchanged);
        $this->line('  skipped:   ' . $skipped);

        if (!empty($samples)) {
            $this->newLine();
            $this->info('Sample changes (up to 25):');
            foreach ($samples as $s) {
                $this->line('#' . $s['id'] . ' [' . $s['disk'] . '] ' . $s['old'] . ' => ' . $s['new']);
            }
        }

        if (!$apply) {
            $this->newLine();
            $this->comment('Run again with --apply to persist changes.');
        }

        return self::SUCCESS;
    }

    private function normalizePath(string $path, string $disk): ?string
    {
        $raw = str_replace('\\', '/', trim($path));

        if ($raw === '') return null;

        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $parts = @parse_url($raw);
            $p = is_array($parts) ? ($parts['path'] ?? '') : '';
            $p = is_string($p) ? $p : '';

            $p = ltrim($p, '/');
            if ($p !== '' && Str::startsWith($p, 'storage/')) {
                $raw = $p;
            } else {
                return null;
            }
        } elseif (Str::startsWith($raw, '//')) {
            $raw = ltrim($raw, '/');
            if (!Str::startsWith($raw, 'storage/')) {
                return null;
            }
        }

        if (Str::startsWith($raw, '/')) {
            $raw = ltrim($raw, '/');
        }

        if (Str::startsWith($raw, 'public/')) {
            $raw = Str::replaceFirst('public/', '', $raw);
        }

        if (Str::startsWith($raw, 'storage/')) {
            $raw = Str::replaceFirst('storage/', '', $raw);
        }

        if (Str::startsWith($raw, 'media/')) {
            return $raw;
        }

        $clean = ltrim($raw, '/');

        if (!Str::contains($clean, '/')) {
            if (Storage::disk($disk)->exists('media/' . $clean)) {
                return 'media/' . $clean;
            }

            return $clean;
        }

        return $clean;
    }
}

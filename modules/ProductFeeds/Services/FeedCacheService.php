<?php

namespace Modules\ProductFeeds\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class FeedCacheService
{
    public function __construct(private readonly Filesystem $files)
    {
    }

    public function isEnabled(): bool
    {
        return (bool) setting('product_feeds.cache.enabled', false);
    }

    public function getCachePath(string $channel): string
    {
        $directory = storage_path('app/feeds');

        return $directory . DIRECTORY_SEPARATOR . match ($channel) {
            'google' => 'google.xml',
            'meta' => 'meta.json',
            default => Str::slug($channel) . '.cache',
        };
    }

    public function getMetaPath(string $channel): string
    {
        $directory = storage_path('app/feeds');

        return $directory . DIRECTORY_SEPARATOR . match ($channel) {
            'google' => 'google.meta.json',
            'meta' => 'meta.meta.json',
            default => Str::slug($channel) . '.meta.json',
        };
    }

    public function shouldRegenerate(string $channel): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        $path = $this->getCachePath($channel);

        if (! $this->files->exists($path)) {
            return true;
        }

        $minutes = (int) match ($channel) {
            'google' => setting('product_feeds.cache.google', 60),
            'meta' => setting('product_feeds.cache.meta', 60),
            default => 60,
        };

        if ($minutes <= 0) {
            return true;
        }

        $modified = $this->files->lastModified($path);

        return $modified + ($minutes * 60) < time();
    }

    public function writeCache(string $channel, string $content): void
    {
        $directory = storage_path('app/feeds');

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $this->files->put($this->getCachePath($channel), $content, true);
    }

    public function writeCacheAtomic(string $channel, callable $writer, array &$meta = []): void
    {
        $directory = storage_path('app/feeds');

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $finalPath = $this->getCachePath($channel);
        $tmpPath = $finalPath . '.tmp';
        $bakPath = $finalPath . '.bak';
        $startedAt = microtime(true);
        $errorsCount = 0;
        $lastError = null;

        try {
            $handle = fopen($tmpPath, 'wb');

            if ($handle === false) {
                throw new \RuntimeException('Unable to open feed tmp file for writing: ' . $tmpPath);
            }

            try {
                $writer($handle);
            } finally {
                fclose($handle);
            }

            $hadBackup = false;
            if ($this->files->exists($finalPath)) {
                if ($this->files->exists($bakPath)) {
                    $this->files->delete($bakPath);
                }

                $this->files->move($finalPath, $bakPath);
                $hadBackup = true;
            }

            try {
                $this->files->move($tmpPath, $finalPath);
            } catch (Throwable $e) {
                if ($hadBackup) {
                    try {
                        if ($this->files->exists($finalPath)) {
                            $this->files->delete($finalPath);
                        }
                        $this->files->move($bakPath, $finalPath);
                    } catch (Throwable $ignored) {
                    }
                }

                throw $e;
            }

            if ($hadBackup) {
                try {
                    if ($this->files->exists($bakPath)) {
                        $this->files->delete($bakPath);
                    }
                } catch (Throwable $ignored) {
                }
            }
        } catch (Throwable $e) {
            $errorsCount = 1;
            $lastError = $e->getMessage();

            try {
                if ($this->files->exists($tmpPath)) {
                    $this->files->delete($tmpPath);
                }
            } catch (Throwable $ignored) {
            }

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $meta = array_merge($meta, [
                'generated_at' => Carbon::now()->toIso8601String(),
                'duration_ms' => $durationMs,
                'items_count' => (int) ($meta['items_count'] ?? 0),
                'errors_count' => $errorsCount,
                'last_error' => $lastError,
            ]);
            $this->writeMetaAtomic($channel, $meta);

            throw $e;
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $meta = array_merge($meta, [
            'generated_at' => Carbon::now()->toIso8601String(),
            'duration_ms' => $durationMs,
            'items_count' => (int) ($meta['items_count'] ?? 0),
            'errors_count' => 0,
        ]);

        $this->writeMetaAtomic($channel, $meta);
    }

    public function readMeta(string $channel): ?array
    {
        $path = $this->getMetaPath($channel);

        if (! $this->files->exists($path)) {
            return null;
        }

        $raw = $this->files->get($path);
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function writeMetaAtomic(string $channel, array $meta): void
    {
        $directory = storage_path('app/feeds');

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $finalPath = $this->getMetaPath($channel);
        $tmpPath = $finalPath . '.tmp';
        $bakPath = $finalPath . '.bak';
        $content = json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($content === false) {
            $content = '{"errors_count":1,"last_error":"Failed to encode meta json"}';
        }

        $this->files->put($tmpPath, $content, true);

        $hadBackup = false;
        if ($this->files->exists($finalPath)) {
            try {
                if ($this->files->exists($bakPath)) {
                    $this->files->delete($bakPath);
                }

                $this->files->move($finalPath, $bakPath);
                $hadBackup = true;
            } catch (Throwable $e) {
                // If we can't move the existing meta file (locked), we will retry the final move below.
            }
        }

        $moved = false;
        $attempts = 0;
        while (! $moved && $attempts < 5) {
            $attempts++;

            try {
                $this->files->move($tmpPath, $finalPath);
                $moved = true;
            } catch (Throwable $e) {
                usleep(150000);
            }
        }

        if (! $moved) {
            if ($hadBackup) {
                try {
                    if ($this->files->exists($finalPath)) {
                        $this->files->delete($finalPath);
                    }
                    $this->files->move($bakPath, $finalPath);
                } catch (Throwable $ignored) {
                }
            }

            try {
                if ($this->files->exists($tmpPath)) {
                    $this->files->delete($tmpPath);
                }
            } catch (Throwable $ignored) {
            }

            throw new \RuntimeException('Unable to write meta file atomically: ' . $finalPath);
        }

        if ($hadBackup) {
            try {
                if ($this->files->exists($bakPath)) {
                    $this->files->delete($bakPath);
                }
            } catch (Throwable $ignored) {
            }
        }
    }

    public function readCache(string $channel): ?string
    {
        $path = $this->getCachePath($channel);

        if (! $this->files->exists($path)) {
            return null;
        }

        return $this->files->get($path);
    }

    public function refresh(string $channel, callable $generator): string
    {
        $content = (string) $generator();

        $this->writeCache($channel, $content);

        return $content;
    }
}

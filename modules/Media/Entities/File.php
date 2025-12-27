<?php

namespace Modules\Media\Entities;

use Modules\Media\IconResolver;
use Modules\User\Entities\User;
use Illuminate\Http\JsonResponse;
use Modules\Media\Admin\MediaTable;
use Modules\Support\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
{
    protected static array $ikasSrcsetCache = [];

    protected static ?array $ikasPoolWidthsCache = null;

    protected static array $ikasFormatAvailableCache = [];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'filename',
        'path',
        'url',
        'listing_avif_srcset',
        'listing_webp_srcset',
        'listing_jpeg_srcset',
        'card_webp_url',
        'card_avif_url',
        'card_jpeg_url',
        'card_2x_webp_url',
        'card_2x_avif_url',
        'card_2x_jpeg_url',
        'card_3x_webp_url',
        'card_3x_avif_url',
        'card_3x_jpeg_url',
        'grid_webp_url',
        'grid_avif_url',
        'grid_jpeg_url',
        'grid_2x_webp_url',
        'grid_2x_avif_url',
        'grid_2x_jpeg_url',
        'grid_3x_webp_url',
        'grid_3x_avif_url',
        'grid_3x_jpeg_url',
        'fast_webp_url',
        'fast_avif_url',
        'thumb_webp_url',
        'thumb_avif_url',
        'thumb_jpeg_url',
        'thumb_2x_webp_url',
        'thumb_2x_avif_url',
        'thumb_2x_jpeg_url',
        'detail_webp_url',
        'detail_avif_url',
        'detail_jpeg_url',
        'detail_2x_webp_url',
        'detail_2x_avif_url',
        'detail_2x_jpeg_url',
    ];

    protected $appends = [
        'url',
        'ikas_avif_srcset',
        'ikas_webp_srcset',
        'ikas_jpeg_srcset',
        'listing_avif_srcset',
        'listing_webp_srcset',
        'listing_jpeg_srcset',
        'card_webp_url',
        'card_avif_url',
        'card_jpeg_url',
        'card_2x_webp_url',
        'card_2x_avif_url',
        'card_2x_jpeg_url',
        'card_3x_webp_url',
        'card_3x_avif_url',
        'card_3x_jpeg_url',
        'grid_webp_url',
        'grid_avif_url',
        'grid_jpeg_url',
        'grid_2x_webp_url',
        'grid_2x_avif_url',
        'grid_2x_jpeg_url',
        'grid_3x_webp_url',
        'grid_3x_avif_url',
        'grid_3x_jpeg_url',
        'fast_webp_url',
        'fast_avif_url',
        'thumb_webp_url',
        'thumb_avif_url',
        'thumb_jpeg_url',
        'thumb_2x_webp_url',
        'thumb_2x_avif_url',
        'thumb_2x_jpeg_url',
        'detail_webp_url',
        'detail_avif_url',
        'detail_jpeg_url',
        'detail_2x_webp_url',
        'detail_2x_avif_url',
        'detail_2x_jpeg_url',
    ];

    public function getIkasAvifSrcsetAttribute(): string
    {
        return $this->buildIkasPoolSrcset('avif');
    }

    public function getIkasWebpSrcsetAttribute(): string
    {
        return $this->buildIkasPoolSrcset('webp');
    }

    public function getIkasJpegSrcsetAttribute(): string
    {
        return $this->buildIkasPoolSrcset('jpg');
    }

    public function getListingAvifSrcsetAttribute(): string
    {
        return $this->filterListingSrcset($this->getIkasAvifSrcsetAttribute());
    }

    public function getListingWebpSrcsetAttribute(): string
    {
        return $this->filterListingSrcset($this->getIkasWebpSrcsetAttribute());
    }

    public function getListingJpegSrcsetAttribute(): string
    {
        return $this->filterListingSrcset($this->getIkasJpegSrcsetAttribute());
    }

    protected function filterListingSrcset(string $srcset): string
    {
        $srcset = trim($srcset);
        if ($srcset === '') {
            return '';
        }

        $allowed = array_fill_keys([180, 256, 360, 540, 720, 900, 1080], true);

        $out = [];
        foreach (explode(',', $srcset) as $part) {
            $part = trim($part);
            if ($part === '') continue;

            // Expected format: "URL WIDTHw"
            if (preg_match('/\s(\d+)w$/', $part, $m)) {
                $w = (int) $m[1];
                if (isset($allowed[$w])) {
                    $out[] = $part;
                }
            }
        }

        return implode(', ', $out);
    }

    protected function buildIkasPoolSrcset(string $format): string
    {
        $cacheKey = (string) ($this->id ?? '0') . ':' . strtolower($format);
        if (isset(static::$ikasSrcsetCache[$cacheKey])) {
            return static::$ikasSrcsetCache[$cacheKey];
        }

        $format = strtolower($format);
        if ($format === 'avif' && !(bool) config('image_optimization.ikas_pool.enable_avif', true)) {
            return static::$ikasSrcsetCache[$cacheKey] = '';
        }

        // Avoid broken images for legacy media that doesn't have generated variants yet.
        // If we emit srcset with only non-existing URLs, the browser may pick one and fail hard.
        $availabilityKey = (string) ($this->id ?? '0') . ':' . $format . ':available';
        if (!array_key_exists($availabilityKey, static::$ikasFormatAvailableCache)) {
            $probeWidthsRaw = [
                (int) config('image_optimization.variants.widths.thumb', 180),
                (int) config('image_optimization.variants.widths.card', 260),
                (int) config('image_optimization.variants.widths.grid', 400),
                (int) config('image_optimization.fast_listing.width', config('image_optimization.variants.widths.grid', 400)),
                (int) config('image_optimization.variants.widths.detail', 1000),
            ];

            $probeWidths = array_values(array_unique(array_filter(array_map('intval', $probeWidthsRaw))));

            $available = false;
            foreach ($probeWidths as $w) {
                if ($w <= 0) continue;
                $probe = media_variant_url($this, $w, $format);
                if ($probe) {
                    $available = true;
                    break;
                }
            }

            static::$ikasFormatAvailableCache[$availabilityKey] = $available;
        }

        if (!static::$ikasFormatAvailableCache[$availabilityKey]) {
            return static::$ikasSrcsetCache[$cacheKey] = '';
        }

        if (static::$ikasPoolWidthsCache === null) {
            $pool = array_merge(
                (array) config('image_optimization.ikas_pool.widths', []),
                (array) config('image_optimization.ikas_pool.legacy_widths', [])
            );
            $pool = array_values(array_unique(array_filter(array_map('intval', $pool))));
            sort($pool);
            static::$ikasPoolWidthsCache = $pool;
        }

        $pool = static::$ikasPoolWidthsCache;

        $entries = [];
        foreach ($pool as $w) {
            if ($w <= 0) continue;
            if ((bool) config('image_optimization.ikas_pool.srcset_no_exists_check', true)) {
                $url = media_variant_url_no_check($this, $w, $format);
            } else {
                $url = media_variant_url($this, $w, $format);
            }
            if (!$url) continue;
            $entries[] = $url . ' ' . $w . 'w';
        }

        return static::$ikasSrcsetCache[$cacheKey] = implode(', ', $entries);
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function ($file) {
            $raw = (string) $file->getRawOriginal('path');
            $normalized = $file->normalizeStoragePath($raw);
            Storage::disk($file->disk)->delete($normalized);
        });
    }


    protected function normalizeStoragePath(string $path): string
    {
        $raw = str_replace('\\', '/', $path);

        if ($raw === '') return $raw;

        if (Str::startsWith($raw, ['http://', 'https://', '//'])) return $raw;

        if (Str::startsWith($raw, ['/media/', 'media/'])) return ltrim($raw, '/');

        if (Str::startsWith($raw, ['/storage/', 'storage/'])) {
            $raw = ltrim($raw, '/');
            $raw = Str::replaceFirst('storage/', '', $raw);
            return $raw;
        }

        $clean = Str::startsWith($raw, 'public/') ? Str::replaceFirst('public/', '', $raw) : $raw;
        $clean = ltrim($clean, '/');

        if (Str::contains($clean, '/')) return $clean;

        $disk = $this->disk ?: 'public';
        if (Storage::disk($disk)->exists('media/' . $clean)) {
            return 'media/' . $clean;
        }

        return $clean;
    }


    /**
     * Get the user that uploaded the file.
     *
     * @return void
     */
    public function uploader()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the file's path.
     *
     * @param string $path
     *
     * @return string|null
     */
    public function getPathAttribute($path)
    {
        if (is_null($path)) return null;
        $raw = str_replace('\\', '/', $path);
        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $parts = @parse_url($raw);
            $p = is_array($parts) ? ($parts['path'] ?? '') : '';
            $p = is_string($p) ? $p : '';
            $p = '/' . ltrim($p, '/');

            if (Str::startsWith($p, '/storage/')) {
                $relative = ltrim($p, '/');
                $relative = Str::replaceFirst('storage/', '', $relative);

                if (!Str::contains($relative, '/') && Storage::disk($this->disk ?: 'public')->exists('media/' . $relative)) {
                    return url('storage/media/' . $relative);
                }
            }

            return str_replace('/media/', '/storage/', $raw);
        }

        if (Str::startsWith($raw, ['//'])) {
            return str_replace('/media/', '/storage/', $raw);
        }

        if (Str::startsWith($raw, '/media/')) {
            $raw = Str::replaceFirst('/media/', '/storage/', $raw);
        } elseif (Str::startsWith($raw, 'media/')) {
            $raw = Str::replaceFirst('media/', 'storage/', $raw);
        }

        if (Str::startsWith($raw, ['/storage/', 'storage/'])) {
            $relative = ltrim($raw, '/');
            $relative = Str::replaceFirst('storage/', '', $relative);

            if (!Str::contains($relative, '/') && Storage::disk($this->disk ?: 'public')->exists('media/' . $relative)) {
                return url('storage/media/' . $relative);
            }

            return url($raw);
        }

        $clean = $this->normalizeStoragePath($raw);
        return Storage::disk($this->disk ?: 'public')->url($clean);
    }


    public function getUrlAttribute(): ?string
    {
        $raw = $this->getRawOriginal('path');
        if (is_null($raw)) return null;
        $raw = str_replace('\\', '/', $raw);
        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $parts = @parse_url($raw);
            $p = is_array($parts) ? ($parts['path'] ?? '') : '';
            $p = is_string($p) ? $p : '';
            $p = '/' . ltrim($p, '/');

            if (Str::startsWith($p, '/storage/')) {
                $relative = ltrim($p, '/');
                $relative = Str::replaceFirst('storage/', '', $relative);

                if (!Str::contains($relative, '/') && Storage::disk($this->disk ?: 'public')->exists('media/' . $relative)) {
                    return url('storage/media/' . $relative);
                }
            }

            return str_replace('/media/', '/storage/', $raw);
        }

        if (Str::startsWith($raw, ['//'])) {
            return str_replace('/media/', '/storage/', $raw);
        }

        if (Str::startsWith($raw, '/media/')) {
            $raw = Str::replaceFirst('/media/', '/storage/', $raw);
        } elseif (Str::startsWith($raw, 'media/')) {
            $raw = Str::replaceFirst('media/', 'storage/', $raw);
        }

        if (Str::startsWith($raw, ['/storage/', 'storage/'])) {
            $relative = ltrim($raw, '/');
            $relative = Str::replaceFirst('storage/', '', $relative);

            if (!Str::contains($relative, '/') && Storage::disk($this->disk ?: 'public')->exists('media/' . $relative)) {
                return url('storage/media/' . $relative);
            }

            return url($raw);
        }

        $clean = $this->normalizeStoragePath($raw);
        return Storage::disk($this->disk ?: 'public')->url($clean);
    }


    /**
     * Get file's real path.
     *
     * @return void
     */
    public function realPath()
    {
        if (!is_null($this->attributes['path'])) {
            $normalized = $this->normalizeStoragePath((string) $this->attributes['path']);
            return Storage::disk($this->disk)->path($normalized);
        }
    }


    /**
     * Determine if the file type is image.
     *
     * @return bool
     */
    public function isImage()
    {
        return strtok($this->mime, '/') === 'image';
    }


    /**
     * Get the file's icon.
     *
     * @return string
     */
    public function icon()
    {
        return IconResolver::resolve($this->mime);
    }

    public function getCardWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.card', 260);
        return media_variant_url($this, $w, 'webp');
    }

    public function getCardAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.card', 260);
        return media_variant_url($this, $w, 'avif');
    }

    public function getCardJpegUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.card', 260);
        return media_variant_url($this, $w, 'jpg');
    }

    public function getCard2xWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.card_2x', 520);
        return media_variant_url($this, $w, 'webp');
    }

    public function getCard2xAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.card_2x', 520);
        return media_variant_url($this, $w, 'avif');
    }

    public function getCard2xJpegUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.card_2x', 520);
        return media_variant_url($this, $w, 'jpg');
    }

    public function getCard3xWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.card_3x', 780);
        return media_variant_url($this, $w, 'webp');
    }

    public function getCard3xAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.card_3x', 780);
        return media_variant_url($this, $w, 'avif');
    }

    public function getCard3xJpegUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.card_3x', 780);
        return media_variant_url($this, $w, 'jpg');
    }

    public function getGridWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.grid', 400);
        return media_variant_url($this, $w, 'webp');
    }

    public function getGridAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.grid', 400);
        return media_variant_url($this, $w, 'avif');
    }

    public function getGridJpegUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.grid', 400);
        return media_variant_url($this, $w, 'jpg');
    }

    public function getGrid2xWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.grid_2x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'webp') : null;
    }

    public function getGrid2xAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.grid_2x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'avif') : null;
    }

    public function getGrid2xJpegUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.grid_2x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'jpg') : null;
    }

    public function getGrid3xWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.grid_3x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'webp') : null;
    }

    public function getGrid3xAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.grid_3x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'avif') : null;
    }

    public function getGrid3xJpegUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.grid_3x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'jpg') : null;
    }

    public function getFastWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.fast_listing.width', config('image_optimization.variants.widths.grid', 400));
        return media_variant_url_with_suffix($this, $w, 'webp', 'fast');
    }

    public function getFastAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.fast_listing.width', config('image_optimization.variants.widths.grid', 400));
        return media_variant_url_with_suffix($this, $w, 'avif', 'fast');
    }

    public function getThumbWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.thumb', 80);
        return media_variant_url($this, $w, 'webp');
    }

    public function getThumbAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.thumb', 80);
        return media_variant_url($this, $w, 'avif');
    }

    public function getThumbJpegUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.thumb', 80);
        return media_variant_url($this, $w, 'jpg');
    }

    public function getThumb2xWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.thumb_2x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'webp') : null;
    }

    public function getThumb2xAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.thumb_2x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'avif') : null;
    }

    public function getThumb2xJpegUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.thumb_2x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'jpg') : null;
    }

    public function getDetailWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.detail', 1000);
        return media_variant_url($this, $w, 'webp');
    }

    public function getDetailAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.detail', 1000);
        return media_variant_url($this, $w, 'avif');
    }

    public function getDetailJpegUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.detail', 1000);
        return media_variant_url($this, $w, 'jpg');
    }

    public function getDetail2xWebpUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.detail_2x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'webp') : null;
    }

    public function getDetail2xAvifUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.detail_2x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'avif') : null;
    }

    public function getDetail2xJpegUrlAttribute(): ?string
    {
        $w = (int) config('image_optimization.variants.widths.detail_2x', 0);
        return $w > 0 ? media_variant_url($this, $w, 'jpg') : null;
    }


    /**
     * Get table data for the resource
     *
     * @return JsonResponse
     */
    public function table($request)
    {
        $query = $this->newQuery()
            ->when(!is_null($request->type) && $request->type !== 'null', function ($query) use ($request) {
                $query->where('mime', 'LIKE', "{$request->type}/%");
            });

        return new MediaTable($query);
    }
}

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
        'fast_webp_url',
        'fast_avif_url',
        'thumb_webp_url',
        'thumb_avif_url',
        'thumb_jpeg_url',
        'detail_webp_url',
        'detail_avif_url',
        'detail_jpeg_url',
    ];

    protected $appends = [
        'url',
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
        'fast_webp_url',
        'fast_avif_url',
        'thumb_webp_url',
        'thumb_avif_url',
        'thumb_jpeg_url',
        'detail_webp_url',
        'detail_avif_url',
        'detail_jpeg_url',
    ];


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
        return media_variant_url($this, 260, 'webp');
    }

    public function getCardAvifUrlAttribute(): ?string
    {
        return media_variant_url($this, 260, 'avif');
    }

    public function getCardJpegUrlAttribute(): ?string
    {
        return media_variant_url($this, 260, null);
    }

    public function getCard2xWebpUrlAttribute(): ?string
    {
        return media_variant_url($this, 520, 'webp');
    }

    public function getCard2xAvifUrlAttribute(): ?string
    {
        return media_variant_url($this, 520, 'avif');
    }

    public function getCard2xJpegUrlAttribute(): ?string
    {
        return media_variant_url($this, 520, null);
    }

    public function getCard3xWebpUrlAttribute(): ?string
    {
        return media_variant_url($this, 780, 'webp');
    }

    public function getCard3xAvifUrlAttribute(): ?string
    {
        return media_variant_url($this, 780, 'avif');
    }

    public function getCard3xJpegUrlAttribute(): ?string
    {
        return media_variant_url($this, 780, null);
    }

    public function getGridWebpUrlAttribute(): ?string
    {
        return media_variant_url($this, 400, 'webp');
    }

    public function getGridAvifUrlAttribute(): ?string
    {
        return media_variant_url($this, 400, 'avif');
    }

    public function getGridJpegUrlAttribute(): ?string
    {
        return media_variant_url($this, 400, null);
    }

    public function getFastWebpUrlAttribute(): ?string
    {
        return media_variant_url_with_suffix($this, 400, 'webp', 'fast');
    }

    public function getFastAvifUrlAttribute(): ?string
    {
        return media_variant_url_with_suffix($this, 400, 'avif', 'fast');
    }

    public function getThumbWebpUrlAttribute(): ?string
    {
        return media_variant_url($this, 80, 'webp');
    }

    public function getThumbAvifUrlAttribute(): ?string
    {
        return media_variant_url($this, 80, 'avif');
    }

    public function getThumbJpegUrlAttribute(): ?string
    {
        return media_variant_url($this, 80, null);
    }

    public function getDetailWebpUrlAttribute(): ?string
    {
        return media_variant_url($this, 1000, 'webp');
    }

    public function getDetailAvifUrlAttribute(): ?string
    {
        return media_variant_url($this, 1000, 'avif');
    }

    public function getDetailJpegUrlAttribute(): ?string
    {
        return media_variant_url($this, 1000, null);
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

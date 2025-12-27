<?php

namespace Modules\Tag\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class TagBadge extends Model
{
    protected $table = 'tag_badges';

    protected static function booted()
    {
        static::updating(function (self $badge) {
            if ($badge->isDirty('image_path')) {
                static::deleteImageFile($badge->getOriginal('image_path'));
            }
        });

        static::saved(function () {
            static::bumpCacheVersion();
        });

        static::deleted(function (self $badge) {
            static::deleteImageFile($badge->image_path);
            static::bumpCacheVersion();
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'tag_id',
        'image_path',
        'is_active',
        'show_on_listing',
        'listing_position',
        'show_on_detail',
        'detail_position',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'show_on_listing' => 'bool',
        'show_on_detail' => 'bool',
    ];

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }

        return asset($this->image_path);
    }

    public function positionFor(string $context): string
    {
        return $context === 'detail'
            ? $this->detail_position
            : $this->listing_position;
    }

    public static function forTagIds(array $tagIds, string $context)
    {
        $locale = function_exists('locale') ? locale() : app()->getLocale();
        $tagIds = array_values(array_filter($tagIds));
        sort($tagIds);

        $version = Cache::store('file')->get(static::cacheVersionKey(), 1);
        $key = 'storefront:globals:' . $locale . ':tag_badges:' . $context . ':' . md5(json_encode($tagIds)) . ':v' . $version;

        return Cache::store('file')->remember($key, now()->addMinutes(10), function () use ($tagIds, $context) {
            $query = static::active()->whereIn('tag_id', $tagIds);

            if ($context === 'detail') {
                $query->where('show_on_detail', true);
            } else {
                $query->where('show_on_listing', true);
            }

            return $query->orderByDesc('priority')->get();
        });
    }

    protected static function cacheVersionKey(): string
    {
        return 'storefront:globals:tag_badges:version';
    }

    protected static function bumpCacheVersion(): void
    {
        Cache::store('file')->increment(static::cacheVersionKey());
    }

    protected static function deleteImageFile(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

<?php

namespace Modules\Support\Entities;

use Illuminate\Database\Eloquent\Model;

class UrlSlug extends Model
{
    protected $fillable = ['slug', 'type', 'entity_id'];

    /**
     * Reserved slugs that cannot be used
     */
    const RESERVED_SLUGS = [
        'admin', 'api', 'storage', 'cart', 'checkout', 'account', 
        'assets', 'media', 'sitemap.xml', 'robots.txt', 'login', 
        'register', 'products', 'categories', 'brands', 'password',
        'email', 'logout', 'install', 'license', 'telescope', 
        'debugbar', '_debugbar', 'storefront'
    ];

    /**
     * Check if a slug is reserved
     */
    public static function isReserved(string $slug): bool
    {
        return in_array(strtolower($slug), self::RESERVED_SLUGS);
    }

    /**
     * Check if a slug is available
     */
    public static function isAvailable(string $slug, ?string $type = null, ?int $ignoreEntityId = null): bool
    {
        if (self::isReserved($slug)) {
            return false;
        }

        $query = self::where('slug', $slug);

        // If checking for a specific entity update, exclude that entity from the check
        if ($type && $ignoreEntityId) {
            $query->where(function($q) use ($type, $ignoreEntityId) {
                $q->where('type', '!=', $type)
                  ->orWhere(function($q2) use ($type, $ignoreEntityId) {
                      $q2->where('type', $type)
                         ->where('entity_id', '!=', $ignoreEntityId);
                  });
            });
        }

        return !$query->exists();
    }

    /**
     * Reserve a slug for an entity
     */
    public static function reserve(string $slug, string $type, int $entityId): void
    {
        self::updateOrCreate(
            ['type' => $type, 'entity_id' => $entityId],
            ['slug' => $slug]
        );
    }

    /**
     * Release a slug
     */
    public static function release(string $type, int $entityId): void
    {
        self::where('type', $type)
            ->where('entity_id', $entityId)
            ->delete();
    }

    /**
     * Find entity by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->first();
    }
}

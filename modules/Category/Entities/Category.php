<?php

namespace Modules\Category\Entities;

use TypiCMS\NestableTrait;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Carbon;
use Modules\Media\Entities\File;
use Modules\Support\Eloquent\Model;
use Modules\Media\Eloquent\HasMedia;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\CategoryFaq;
use Modules\Support\Eloquent\Sluggable;
use Spatie\Sitemap\Contracts\Sitemapable;
use Modules\Support\Eloquent\Translatable;

class Category extends Model implements Sitemapable
{
    use Translatable, Sluggable, HasMedia, NestableTrait;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'slug', 'position', 'is_searchable', 'is_active', 'meta_title', 'meta_description', 'description', 'faq', 'faq_items'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['translations'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_searchable' => 'boolean',
        'is_active' => 'boolean',
        'faq_items' => 'array',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translatedAttributes = ['name'];

    /**
     * The attribute that will be slugged.
     *
     * @var string
     */
    protected $slugAttribute = 'name';


    public static function findBySlug($slug)
    {
        return static::with('files')->where('slug', $slug)->firstOrNew([]);
    }


    public static function tree()
    {
        $key = 'storefront:globals:' . locale() . ':categories:tree:v1';

        return Cache::store('file')->remember($key, now()->addMinutes(10), function () {
            return static::with('files')
                ->orderByRaw('-position DESC')
                ->get()
                ->nest();
        });
    }


    public static function treeList()
    {
        $key = 'storefront:globals:' . locale() . ':categories:tree_list:v1';

        return Cache::store('file')->remember($key, now()->addMinutes(10), function () {
            return static::orderByRaw('-position DESC')
                ->get()
                ->nest()
                ->setIndent('¦–– ')
                ->listsFlattened('name');
        });
    }

    public static function keyValuedTreeList()
    {
        $key = 'storefront:globals:' . locale() . ':categories:key_valued_tree_list:v1';

        return Cache::store('file')->remember($key, now()->addMinutes(10), function () {
            $categories = static::orderByRaw('-position DESC')
                ->get()
                ->nest()
                ->setIndent('¦–– ')
                ->listsFlattened('name');

            return collect($categories)
                ->map(function ($key, $value) {
                    return [
                        'name' => $key,
                        'value' => $value,
                    ];
                })
                ->values();
        });
    }


    public static function searchable()
    {
        $key = 'storefront:globals:' . locale() . ':categories:searchable:v1';

        return Cache::store('file')->remember($key, now()->addMinutes(10), function () {
            return static::where('is_searchable', true)
                ->get()
                ->map(function ($category) {
                    return [
                        'slug' => $category->slug,
                        'name' => $category->name,
                    ];
                });
        });
    }


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addActiveGlobalScope();

        static::deleting(function (Category $category) {
            \Modules\Product\Entities\Product::where('primary_category_id', $category->id)
                ->chunkById(100, function ($products) use ($category) {
                    foreach ($products as $product) {
                        $remaining = $product->categories()
                            ->where('categories.id', '!=', $category->id)
                            ->orderBy('categories.position', 'asc')
                            ->pluck('categories.id')
                            ->all();

                        $next = $remaining[0] ?? null;

                        $product->withoutEvents(function () use ($product, $next) {
                            $product->update(['primary_category_id' => $next]);
                        });
                    }
                });
        });
    }


    public function isRoot()
    {
        return $this->exists && is_null($this->parent_id);
    }


    public function url()
    {
        return route('categories.products.index', ['category' => $this->slug]);
    }


    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function primaryProducts()
    {
        return $this->hasMany(Product::class, 'primary_category_id');
    }


    public function faqs()
    {
        return $this->hasMany(CategoryFaq::class)->orderBy('position');
    }


    public function descendantsAndSelfIds()
    {
        $all = static::query()->select('id', 'parent_id')->get();

        $ids = [];
        $stack = [$this->id];

        while (! empty($stack)) {
            $parentId = array_pop($stack);

            if (in_array($parentId, $ids, true)) {
                continue;
            }

            $ids[] = $parentId;

            foreach ($all->where('parent_id', $parentId) as $child) {
                $stack[] = $child->id;
            }
        }

        return collect($ids);
    }


    public function getLogoAttribute()
    {
        return $this->files->where('pivot.zone', 'logo')->first() ?: new File;
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo ? $this->logo->path : null;
    }

    public function getFaqJsonArrayAttribute(): array
    {
        $raw = $this->faq;

        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'q' => 'Genel Bilgi',
                'a' => (string) $raw,
            ],
        ];
    }

    public function getBannerAttribute()
    {
        return $this->files->where('pivot.zone', 'banner')->first() ?: new File;
    }


    public function toArray()
    {
        $attributes = parent::toArray();

        if ($this->relationLoaded('files')) {
            $attributes += [
                'logo' => [
                    'id' => $this->logo->id,
                    'path' => $this->logo->path,
                    'exists' => $this->logo->exists,
                ],
                'banner' => [
                    'id' => $this->banner->id,
                    'path' => $this->banner->path,
                    'exists' => $this->banner->exists,
                ],
            ];
        }

        return $attributes;
    }


    public function toSitemapTag(): Url|string|array
    {
        $changefreq = setting('support.sitemap.categories_changefreq', Url::CHANGE_FREQUENCY_DAILY);
        $priority = (float) setting('support.sitemap.categories_priority', 0.8);

        $url = route('categories.products.index', $this->slug);

        if (! is_string($url) || trim($url) === '' || trim($url) === '#') {
            return [];
        }

        $tag = Url::create($url)
            ->setChangeFrequency($changefreq)
            ->setPriority($priority);

        if (! empty($this->updated_at)) {
            try {
                $tag->setLastModificationDate(
                    $this->updated_at instanceof \DateTimeInterface
                        ? $this->updated_at
                        : Carbon::create($this->updated_at)
                );
            } catch (\Throwable $e) {
            }
        }

        return $tag;
    }
}

<?php

namespace Modules\Product\Entities;

use Illuminate\Http\Request;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\Support\Eloquent\Model;
use Modules\Media\Eloquent\HasMedia;
use Modules\Meta\Eloquent\HasMetaData;
use Modules\Support\Search\Searchable;
use Modules\Product\Admin\ProductTable;
use Modules\Support\Eloquent\Sluggable;
use Spatie\Sitemap\Contracts\Sitemapable;
use Modules\Support\Eloquent\Translatable;
use Modules\Product\Entities\Concerns\IsNew;
use Modules\Product\Entities\Concerns\HasStock;
use Modules\Product\Entities\Concerns\Predicates;
use Modules\Product\Entities\Concerns\Filterable;
use Modules\Product\Entities\Concerns\QueryScopes;
use Modules\Product\Entities\Concerns\ModelMutators;
use Modules\Product\Entities\Concerns\ModelAccessors;
use Modules\Product\Entities\Concerns\HasSpecialPrice;
use Modules\Product\Entities\Concerns\EloquentRelations;
use Modules\Tag\Entities\TagBadge;
use Modules\Unit\Entities\Unit;

class Product extends Model implements Sitemapable
{
    use Translatable,
        Searchable,
        Filterable,
        Sluggable,
        HasMedia,
        HasMetaData,
        HasSpecialPrice,
        HasStock,
        IsNew,
        QueryScopes,
        ModelAccessors,
        ModelMutators,
        Predicates,
        EloquentRelations;

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
    protected $fillable = [
        'brand_id',
        'tax_class_id',
        'sale_unit_id',
        'primary_category_id',
        'google_product_category_path',
        'slug',
        'sku',
        'price',
        'special_price',
        'special_price_type',
        'special_price_start',
        'special_price_end',
        'selling_price',
        'manage_stock',
        'qty',
        'in_stock',
        'is_virtual',
        'is_active',
        'new_from',
        'new_to',
        'list_variants_separately',
        'redirect_type',
        'redirect_target_id',
        'main_page_position',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_virtual' => 'boolean',
        'is_active' => 'boolean',
        'special_price_start' => 'datetime',
        'special_price_end' => 'datetime',
        'new_from' => 'datetime',
        'new_to' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'qty' => 'decimal:2',
        'list_variants_separately' => 'boolean',
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'base_image',
        'additional_images',
        'media',
        'formatted_price',
        'formatted_price_range',
        'has_percentage_special_price',
        'special_price_percent',
        'rating_percent',
        'does_manage_stock',
        'is_in_stock',
        'is_out_of_stock',
        'is_new',
        'variant',
        'unit_min',
        'unit_step',
        'unit_suffix',
        'unit_label',
        'unit_decimal',
        'deleted_at',
        'unit_info_top',
        'unit_info_bottom',
        'unit_default_qty',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected array $translatedAttributes = [
        'name',
        'description',
        'short_description',
    ];


    /**
     * The attribute that will be slugged.
     *
     * @var string
     */
    protected string $slugAttribute = 'name';


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::addActiveGlobalScope();

        static::saved(function ($product) {
            $attributes = request()->all();
            $routeName = optional(request()->route())->getName();

            // Only sync relations when saving via product form
            if (in_array($routeName, ['admin.products.store', 'admin.products.update']) && !empty($attributes)) {
                $product->categories()->sync(array_get($attributes, 'categories', []));
                $product->tags()->sync(array_get($attributes, 'tags', []));
                $product->upSellProducts()->sync(array_get($attributes, 'up_sells', []));
                $product->crossSellProducts()->sync(array_get($attributes, 'cross_sells', []));
                $product->relatedProducts()->sync(array_get($attributes, 'related_products', []));

                $selectedCategories = array_get($attributes, 'categories', []);
                $primary = array_get($attributes, 'primary_category_id');

                if (!empty($primary) && in_array($primary, $selectedCategories)) {
                    $product->withoutEvents(function () use ($product, $primary) {
                        $product->update(['primary_category_id' => $primary]);
                    });
                } elseif (!empty($selectedCategories)) {
                    $fallback = reset($selectedCategories);
                    $product->withoutEvents(function () use ($product, $fallback) {
                        $product->update(['primary_category_id' => $fallback]);
                    });
                } else {
                    $product->withoutEvents(function () use ($product) {
                        $product->update(['primary_category_id' => null]);
                    });
                }
            }

            $product->withoutEvents(function () use ($product) {
                $product->update([
                    'selling_price' => ($product->hasSpecialPrice() ? $product->getSpecialPrice() : $product->price)->amount(),
                ]);
            });
        });
    }


    /**
     * Get active tag badges for this product for the given context.
     *
     * @param string $context
     * @return \Illuminate\Support\Collection
     */
    public function badgeVisualsFor(string $context)
    {
        $tags = $this->relationLoaded('tags')
            ? $this->tags
            : $this->tags()->get();

        if ($tags->isEmpty()) {
            return collect();
        }

        $tagIds = $tags->pluck('id')->all();

        return TagBadge::forTagIds($tagIds, $context);
    }


    /**
     * Get table data for the resource
     *
     * @param Request $request
     *
     * @return ProductTable
     */
    public function table(Request $request): ProductTable
    {
        $pvAgg = DB::table('product_variants as pv')
            ->selectRaw("pv.product_id,
                COUNT(*) as active_variants,
                SUM(CASE WHEN pv.manage_stock = 1 THEN 1 ELSE 0 END) as active_manage_variants,
                SUM(CASE WHEN pv.manage_stock = 0 OR pv.qty > 0 THEN 1 ELSE 0 END) as in_stock_variants,
                SUM(pv.qty) as sum_qty")
            ->whereNull('pv.deleted_at')
            ->where('pv.is_active', 1)
            ->groupBy('pv.product_id');

        $query = $this->newQuery()
            ->withoutGlobalScope('active')
            ->withName()
            ->withBaseImage()
            ->withPrice()
            ->with(['saleUnit','variants','primaryCategory','brand'])
            ->leftJoinSub($pvAgg, 'pv_stats', function ($join) {
                $join->on('products.id', '=', 'pv_stats.product_id');
            })
            ->addSelect(['id', 'slug', 'brand_id', 'primary_category_id', 'is_active', 'in_stock', 'manage_stock', 'qty', 'created_at', 'updated_at'])
            ->addSelect(['sale_unit_id'])
            ->addSelect(DB::raw("CASE
                WHEN COALESCE(pv_stats.active_variants,0) > 0 THEN
                    CASE WHEN COALESCE(pv_stats.in_stock_variants,0) > 0 THEN 1 ELSE 0 END
                ELSE
                    CASE WHEN (products.manage_stock = 0 OR products.qty > 0) THEN 1 ELSE 0 END
            END as stock_sort"))
            ->addSelect(DB::raw("CASE
                WHEN COALESCE(pv_stats.active_variants,0) > 0 THEN COALESCE(pv_stats.sum_qty,0)
                ELSE
                    CASE WHEN products.manage_stock = 1 THEN COALESCE(products.qty,0) ELSE 9999999 END
            END as stock_qty_sort"))
            ->when($request->has('brand_id') && $request->brand_id !== null && $request->brand_id !== '', function ($q) use ($request) {
                $q->where('brand_id', (int) $request->brand_id);
            })
            ->when($request->has('category_id') && $request->category_id !== null && $request->category_id !== '', function ($q) use ($request) {
                $categoryId = (int) $request->category_id;
                $q->where(function ($sub) use ($categoryId) {
                    $sub->where('primary_category_id', $categoryId)
                        ->orWhereHas('categories', function ($cat) use ($categoryId) {
                            $cat->where('categories.id', $categoryId);
                        });
                });
            })
            ->when($request->has('stock') && $request->stock !== null && $request->stock !== '', function ($q) use ($request) {
                $stock = (string) $request->stock;

                if ($stock === 'in') {
                    $q->whereRaw('stock_sort = 1');
                }

                if ($stock === 'out') {
                    $q->whereRaw('stock_sort = 0');
                }
            })
            ->when($request->has('except'), function ($query) use ($request) {
                $query->whereNotIn('id', explode(',', $request->except));
            });

        return new ProductTable($query);
    }


    public function clean(): array
    {
        $data = [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'is_active' => (bool) ($this->is_active ?? true),
            'in_stock' => (bool) ($this->in_stock ?? true),
            'manage_stock' => (bool) ($this->manage_stock ?? false),
            'qty' => $this->qty,
            'is_new' => (bool) $this->is_new,
            'is_in_stock' => (bool) $this->is_in_stock,
            'is_out_of_stock' => (bool) $this->is_out_of_stock,
            'options_count' => (int) $this->options_count,
            'has_percentage_special_price' => (bool) $this->has_percentage_special_price,
            'special_price_percent' => $this->special_price_percent,
            'list_variants_separately' => (bool) ($this->list_variants_separately ?? false),
            'rating_percent' => $this->rating_percent,
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'unit_min' => $this->unit_min,
            'unit_step' => $this->unit_step,
            'unit_suffix' => $this->unit_suffix,
            'unit_label' => $this->unit_label,
            'unit_decimal' => (bool) $this->unit_decimal,
            'unit_info_top' => $this->unit_info_top,
            'unit_info_bottom' => $this->unit_info_bottom,
            'unit_default_qty' => $this->unit_default_qty,
            'does_manage_stock' => (bool) $this->manage_stock,
            'redirect_type' => $this->redirect_type ?? '404',
            'redirect_target_id' => $this->redirect_target_id,
        ];

        $redirectTargetInfo = null;
        if ($this->redirect_target_id) {
            if (str_contains($this->redirect_type ?? '', 'product')) {
                $targetProd = \Modules\Product\Entities\Product::withoutGlobalScope('active')->find($this->redirect_target_id);
                if ($targetProd) {
                    $redirectTargetInfo = ['id' => $targetProd->id, 'name' => $targetProd->name];
                }
            } elseif (str_contains($this->redirect_type ?? '', 'category')) {
                $targetCat = \Modules\Category\Entities\Category::find($this->redirect_target_id);
                if ($targetCat) {
                    $redirectTargetInfo = ['id' => $targetCat->id, 'name' => $targetCat->name];
                }
            }
        }
        $data['redirect_target'] = $redirectTargetInfo;

        foreach (['price', 'special_price', 'selling_price'] as $key) {
            try {
                $val = $this->$key;
                if ($val instanceof \Modules\Support\Money) {
                    $data[$key] = $val->jsonSerialize();
                }
            } catch (\Throwable $e) {}
        }

        $data['variants'] = $this->relationLoaded('variants')
            ? $this->variants->map(fn($v) => $v->clean())->all()
            : [];

        $data['variant'] = $this->relationLoaded('variant') && $this->variant
            ? $this->variant->clean()
            : null;

        $data['variations'] = $this->relationLoaded('variations')
            ? $this->variations->map(fn($v) => [
                'id' => $v->id,
                'uid' => $v->uid,
                'name' => $v->name,
                'type' => $v->type,
                'values' => $v->values->map(fn($val) => [
                    'id' => $val->id,
                    'uid' => $val->uid,
                    'label' => $val->label,
                    'price' => optional($val->price)->jsonSerialize(),
                ])->all(),
            ])->all()
            : [];

        if ($this->relationLoaded('options')) {
            $data['options'] = $this->options->map(fn($o) => [
                'id' => $o->id,
                'name' => $o->name,
                'type' => $o->type,
                'is_required' => $o->is_required,
                'values' => $o->values->map(fn($v) => [
                    'id' => $v->id,
                    'label' => $v->label,
                    'price' => optional($v->price)->jsonSerialize(),
                ])->all(),
            ])->all();
        } else {
            $data['options'] = [];
        }

        if ($this->relationLoaded('files')) {
            $data['base_image'] = $this->base_image;
            $data['additional_images'] = $this->additional_images->all();
        } else {
            $data['base_image'] = null;
            $data['additional_images'] = [];
        }

        // JS expects 'media' array for gallery
        $data['media'] = $this->relationLoaded('files') ? $this->getMediaPayload() : [];

        $data['base_image_thumb'] = [
            'path' => media_variant_url($this->base_image, (int) config('image_optimization.variants.widths.thumb', 80))
        ];

        $data['formatted_price'] = $this->formatted_price;

        return $data;
    }


    private function getMediaPayload()
    {
        return $this->media->values()->all();
    }


    private function cleanMetaText(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        // Remove HTML tags first
        $clean = strip_tags($text);

        // Decode HTML entities like &nbsp;, &ccedil;, &ndash; to real characters
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize NBSP (U+00A0) to normal space
        $clean = str_replace("\xC2\xA0", ' ', $clean);

        // Normalize all whitespace
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? '';

        // Trim and normalize encoding
        $clean = trim($clean);
        if ($clean === '') {
            return '';
        }

        return mb_convert_encoding($clean, 'UTF-8', 'UTF-8');
    }


    public function getSeoMetaDescriptionAttribute(): ?string
    {
        // 1) Highest priority: explicit meta_description from bulk meta system
        $metaDescription = optional($this->meta)->meta_description;
        $metaDescription = $this->cleanMetaText(is_string($metaDescription) ? $metaDescription : null);

        if ($metaDescription !== '') {
            return $metaDescription;
        }

        // 2) Next: short_description (translated)
        $short = $this->cleanMetaText($this->short_description ?? null);

        if ($short !== '' && mb_strlen($short) > 20) {
            return Str::limit($short, 160);
        }

        // 3) Fallback: description (translated), cleaned and limited
        $desc = $this->cleanMetaText($this->description ?? null);

        if ($desc !== '' && mb_strlen($desc) > 20) {
            return Str::limit($desc, 160, '...');
        }

        // 4) Ultimate fallback: Template-based description to avoid thin content
        $name = $this->cleanMetaText($this->name ?? '');
        $category = $this->categories()->first();
        $brand = $this->brand()->first();

        if ($name === '') {
            return null;
        }

        $template = $name;
        if ($category) {
            $template .= " En iyi {$category->name} modelleri";
        }
        if ($brand) {
            $template .= " ({$brand->name})";
        }
        $template .= " en uygun fiyatlarla Kayalar Manifatura'da. Hemen keşfet ve satın al.";

        return Str::limit($template, 160);
    }


    public function url(): string
    {
        // İkas-style clean URL
        return url('/' . $this->slug);
    }


    /**
     * Get the indexable data array for the product.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        # MySQL Full-Text search handles indexing automatically.
        if (config('scout.driver') === 'mysql') {
            return [];
        }

        $translations = $this->translations()
            ->withoutGlobalScope('locale')
            ->get(['name', 'description', 'short_description']);

        return [
            'id' => $this->id,
            'translations' => $translations,
        ];
    }


    public function searchTable(): string
    {
        return 'product_translations';
    }


    public function searchKey(): string
    {
        return 'product_id';
    }


    public function searchColumns(): array
    {
        return ['name'];
    }


    /**
     * Help HasMedia trait to extract media
     * for this model from the HTTP request.
     *
     * @return mixed
     */
    public function extractMediaFromRequest(): mixed
    {
        $hasMedia = request()->has('media');
        $hasDownloads = request()->has('downloads');

        if (!$hasMedia && !$hasDownloads) {
            return [];
        }

        $payload = [];

        if ($hasMedia) {
            $media = collect(request('media', []));

            $payload['base_image'] = $media->first();
            $payload['additional_images'] = $media
                ->except($media->keys()->first())
                ->toArray();
        }

        if ($hasDownloads) {
            $payload['downloads'] = request('downloads', []);
        }

        return $payload;
    }


    public function toSitemapTag(): Url|string|array
    {
        $changefreq = setting('support.sitemap.products_changefreq', Url::CHANGE_FREQUENCY_WEEKLY);
        $priority = (float) setting('support.sitemap.products_priority', 0.7);

        $url = $this->url();

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

    public function saleUnit()
    {
        return $this->belongsTo(\Modules\Unit\Entities\Unit::class, 'sale_unit_id');
    }

    public function primaryCategory()
    {
        return $this->belongsTo(\Modules\Category\Entities\Category::class, 'primary_category_id');
    }

    public function productMedia()
    {
        return $this->hasMany(ProductMedia::class)->orderBy('position');
    }

    public function videos()
    {
        return $this->productMedia()->where('type', 'video');
    }

    public function seoCategory()
    {
        if ($this->primary_category_id) {
            return $this->relationLoaded('primaryCategory') ? $this->primaryCategory : $this->primaryCategory()->first();
        }

        if ($this->relationLoaded('categories')) {
            return $this->categories->sortBy('position')->first();
        }

        return $this->categories()->orderBy('position')->first();
    }

    public function getEffectiveUnit(): Unit
    {
        static $unitCache = [];

        if ($this->sale_unit_id) {
            $unitId = (int) $this->sale_unit_id;

            if ($unitId > 0 && array_key_exists($unitId, $unitCache)) {
                $unit = $unitCache[$unitId];
            } else {
                $unit = $this->relationLoaded('saleUnit') ? $this->saleUnit : $this->saleUnit()->first();

                if ($unitId > 0) {
                    $unitCache[$unitId] = $unit;
                }
            }

            if ($unit) {
                return $unit;
            }
        }

        return new Unit([
            'code' => 'unit_default',
            'name' => 'Default Unit',
            'label' => '',
            'short_suffix' => '',
            'info' => null,
            'info_top' => null,
            'info_bottom' => null,
            'step' => 1,
            'min' => 1,
            'default_qty' => 1,
            'is_default' => true,
            'is_decimal_stock' => false,
        ]);
    }

    public function getUnitMinAttribute(): float
    {
        return (float) ($this->saleUnit?->min ?? 0);
    }

    public function getUnitStepAttribute(): float
    {
        return (float) ($this->saleUnit?->step ?? 1);
    }

    public function getUnitSuffixAttribute(): string
    {
        return $this->saleUnit?->getDisplaySuffix() ?: '';
    }

    public function getUnitLabelAttribute(): string
    {
        return $this->saleUnit?->label ?: '';
    }

    public function getUnitDecimalAttribute(): bool
    {
        return (bool) $this->saleUnit?->isDecimalStock();
    }

    public function getUnitInfoTopAttribute(): ?string
    {
        return $this->saleUnit?->info_top ?? $this->saleUnit?->info;
    }

    public function getUnitInfoBottomAttribute(): ?string
    {
        return $this->saleUnit?->info_bottom;
    }

    public function getUnitDefaultQtyAttribute(): float
    {
        $min = (float) ($this->saleUnit?->min ?? 1);
        $def = (float) ($this->saleUnit?->default_qty ?? $min);
        return max($def, $min);
    }


    public function getFormattedStock(): string
    {
        $stockSource = $this->variant ?? $this; // prefer current variant if available
        $qty = (float) ($stockSource->qty ?? 0);
        $unit = $this->saleUnit;

        $value = fmod($qty, 1) === 0.0
            ? (string) (int) $qty
            : rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');

        $suffix = $unit ? trim($unit->getDisplaySuffix()) : '';

        return $suffix !== '' ? "{$value} {$suffix}" : $value;
    }

    public function getDeletedAtAttribute($value)
    {
        return $value;
    }
}

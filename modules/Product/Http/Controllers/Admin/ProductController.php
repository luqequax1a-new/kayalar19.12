<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Modules\Product\Entities\Product;
use Illuminate\Support\Facades\Mail;
use Modules\Product\Mail\AdminStockAlertMail;
use Modules\Product\Listeners\SendBackInStockNotifications;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Product\Http\Requests\SaveProductRequest;
use Modules\Product\Transformers\ProductEditResource;
use Modules\Product\Services\ProductDuplicator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Modules\Brand\Entities\Brand;
use Modules\Category\Entities\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductController
{
    use HasCrudActions;

    private function getStats(Request $request): array
    {
        $base = Product::query()
            ->withoutGlobalScope('active')
            ->when($request->has('brand_id') && $request->brand_id !== null && $request->brand_id !== '', function (Builder $q) use ($request) {
                $q->where('brand_id', (int) $request->brand_id);
            })
            ->when($request->has('category_id') && $request->category_id !== null && $request->category_id !== '', function (Builder $q) use ($request) {
                $categoryId = (int) $request->category_id;

                $q->where(function ($sub) use ($categoryId) {
                    $sub->where('primary_category_id', $categoryId)
                        ->orWhereHas('categories', function ($cat) use ($categoryId) {
                            $cat->where('categories.id', $categoryId);
                        });
                });
            })
            ->when($request->has('is_active') && $request->is_active !== null && $request->is_active !== '', function (Builder $q) use ($request) {
                $q->where('is_active', (int) $request->is_active);
            })
            ->when($request->has('price_min') && $request->price_min !== null && $request->price_min !== '', function (Builder $q) use ($request) {
                $min = (float) str_replace(',', '.', (string) $request->price_min);
                $q->where('selling_price', '>=', $min);
            })
            ->when($request->has('price_max') && $request->price_max !== null && $request->price_max !== '', function (Builder $q) use ($request) {
                $max = (float) str_replace(',', '.', (string) $request->price_max);
                $q->where('selling_price', '<=', $max);
            })
            ->when($request->has('created_from') && $request->created_from !== null && $request->created_from !== '', function (Builder $q) use ($request) {
                try {
                    $from = \Illuminate\Support\Carbon::parse((string) $request->created_from)->startOfDay();
                    $q->where('created_at', '>=', $from);
                } catch (\Throwable $e) {
                }
            })
            ->when($request->has('created_to') && $request->created_to !== null && $request->created_to !== '', function (Builder $q) use ($request) {
                try {
                    $to = \Illuminate\Support\Carbon::parse((string) $request->created_to)->endOfDay();
                    $q->where('created_at', '<=', $to);
                } catch (\Throwable $e) {
                }
            })
            ->when($request->has('has_special') && $request->has_special !== null && $request->has_special !== '', function (Builder $q) use ($request) {
                $flag = (string) $request->has_special;

                if ($flag === '1') {
                    $now = \Illuminate\Support\Carbon::now();
                    $q->whereNotNull('special_price')
                        ->where('special_price', '>', 0)
                        ->where(function ($sq) use ($now) {
                            $sq->whereNull('special_price_start')
                                ->orWhere('special_price_start', '<=', $now);
                        })
                        ->where(function ($sq) use ($now) {
                            $sq->whereNull('special_price_end')
                                ->orWhere('special_price_end', '>=', $now);
                        });
                }

                if ($flag === '0') {
                    $q->where(function ($sq) {
                        $sq->whereNull('special_price')->orWhere('special_price', '<=', 0);
                    });
                }
            });

        $total = (clone $base)->count();
        $active = (clone $base)->where('is_active', 1)->count();
        $inactive = (clone $base)->where('is_active', 0)->count();

        $pvAgg = DB::table('product_variants as pv')
            ->selectRaw("pv.product_id,
                COUNT(*) as active_variants,
                SUM(CASE WHEN pv.manage_stock = 1 THEN 1 ELSE 0 END) as active_manage_variants,
                SUM(CASE WHEN pv.manage_stock = 0 OR pv.qty > 0 THEN 1 ELSE 0 END) as in_stock_variants,
                SUM(pv.qty) as sum_qty")
            ->whereNull('pv.deleted_at')
            ->where('pv.is_active', 1)
            ->groupBy('pv.product_id');

        $stockBase = (clone $base)
            ->leftJoinSub($pvAgg, 'pv_stats', function ($join) {
                $join->on('products.id', '=', 'pv_stats.product_id');
            });

        $inStockExpr = "(
            (COALESCE(pv_stats.active_variants,0) > 0 AND COALESCE(pv_stats.in_stock_variants,0) > 0)
            OR
            (COALESCE(pv_stats.active_variants,0) = 0 AND (products.manage_stock = 0 OR products.qty > 0))
        )";

        $outOfStockExpr = "(
            (COALESCE(pv_stats.active_variants,0) > 0 AND COALESCE(pv_stats.in_stock_variants,0) = 0)
            OR
            (COALESCE(pv_stats.active_variants,0) = 0 AND products.manage_stock = 1 AND products.qty <= 0)
        )";

        $lowStockExpr = "(
            (COALESCE(pv_stats.active_variants,0) = 0 AND products.manage_stock = 1 AND products.qty > 0 AND products.qty < 10)
            OR
            (COALESCE(pv_stats.active_variants,0) > 0 AND COALESCE(pv_stats.active_manage_variants,0) > 0 AND COALESCE(pv_stats.sum_qty,0) > 0 AND COALESCE(pv_stats.sum_qty,0) < 10)
        )";

        $inStock = (clone $stockBase)->whereRaw($inStockExpr)->distinct('products.id')->count('products.id');
        $outOfStock = (clone $stockBase)->whereRaw($outOfStockExpr)->distinct('products.id')->count('products.id');
        $lowStock = (clone $stockBase)->whereRaw($lowStockExpr)->distinct('products.id')->count('products.id');

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'in_stock' => $inStock,
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock,
        ];
    }

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected string $model = Product::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected string $label = 'product::products.product';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected string $viewPath = 'product::admin.products';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected string|array $validation = SaveProductRequest::class;


    /**
     * Display a listing of the resource with filters.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Support\Collection
     */
    public function index(Request $request)
    {
        // Preserve default search behavior from HasCrudActions
        // Specific request to get variants for a selected product
        if ($request->has('variants_only') && $request->has('product_id')) {
            $product = $this->getModel()->with('variants.files')->find($request->get('product_id'));
            if (!$product) return response()->json([]);
            
            $productImageUrl = $product->base_image->url;
            return $product->variants->map(function($variant) use ($productImageUrl) {
                // For variants: Use variant's selling price (special if active, else normal)
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'price' => (float) $variant->selling_price->amount(),
                    'base_image' => [
                        'path' => $variant->base_image->url ?: $productImageUrl,
                    ],
                ];
            });
        }

        if ($request->has('query') || $request->has('initial')) {
            $searchTerm = $request->get('query');
            
            $query = $this->getModel()
                ->when($searchTerm, function($q) use ($searchTerm) {
                    $q->where(function($sq) use ($searchTerm) {
                        $sq->whereHas('translations', function ($t) use ($searchTerm) {
                            $t->where('name', 'like', "%{$searchTerm}%");
                        })
                        ->orWhere('sku', 'like', "%{$searchTerm}%");
                    });
                })
                ->with(['files', 'variant.files', 'categories']) // Only load default variant for quick fallback
                ->limit($request->get('limit', 15));

            if (!$searchTerm) {
                $query->latest();
            }

            return $query->get()->map(function($product) {
                $defaultVariant = $product->variant;
                
                $price = $product->selling_price;
                $productImageUrl = $product->base_image->url;
                
                if (!$productImageUrl && $defaultVariant) {
                    $productImageUrl = $defaultVariant->base_image->url;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku ?: ($defaultVariant->sku ?? null),
                    'price' => (float) $price->amount(),
                    'formatted_price' => $price->convertToCurrentCurrency()->format(),
                    'is_in_stock' => $product->isInStock(),
                    'category_name' => strval($product->categories->first()?->name ?? ""),
                    'has_variants' => (bool) ($product->variants_count > 0 || $product->variants?->count() > 0),
                    'base_image' => [
                        'path' => $productImageUrl,
                    ],
                ];
            });
        }

        $brands = Brand::list();
        $categories = Category::treeList();
        $taxClasses = \Modules\Tax\Entities\TaxClass::list();
        $units = \Modules\Unit\Entities\Unit::pluck('name', 'id');
        $tags = \Modules\Tag\Entities\Tag::list();
        $attributeSets = \Modules\Attribute\Entities\AttributeSet::with('attributes.values')->get()->sortBy('name');
        $globalVariations = \Modules\Variation\Entities\Variation::where('is_global', 1)->get();
        $globalOptions = \Modules\Option\Entities\Option::where('is_global', 1)->get();

        $stats = $this->getStats($request);

        return view("{$this->viewPath}.index", compact('brands', 'categories', 'stats', 'taxClasses', 'units', 'tags', 'attributeSets', 'globalVariations', 'globalOptions'));
    }

    public function stats(Request $request): JsonResponse
    {
        if (!$request->wantsJson()) {
            return response()->json(['message' => 'Not Acceptable'], 406);
        }

        return response()->json([
            'stats' => $this->getStats($request),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response|JsonResponse
     */
    public function store()
    {
        $this->disableSearchSyncing();

        $entity = $this->getModel()->create(
            $this->getRequest('store')->all()
        );

        $this->syncProductMedia($entity, request('product_media', []));

        $this->searchable($entity);

        $message = trans('admin::messages.resource_created', ['resource' => $this->getLabel()]);

        if (request()->query('exit_flash')) {
            session()->flash('exit_flash', $message);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                    'product_id' => $entity->id,
                    'redirect_url' => route("{$this->getRoutePrefix()}.edit", $entity->id),
                ],
                200
            );
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess($message);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Factory|View|Application
     */
    public function edit($id): Factory|View|Application
    {
        $entity = $this->getEntity($id);
        $productEditResource = new ProductEditResource($entity);

        return view(
            "{$this->viewPath}.edit",
            [
                'product' => $entity,
                'product_resource' => $productEditResource->response()->content(),
            ]
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     */
    public function update($id)
    {
        $entity = $this->getEntity($id);
        $oldSlug = $entity->slug;
        $requestOldSlug = request('original_slug');
        $newSlug = request('slug');
        $shouldCreateRedirect = (bool) request('redirect_on_slug_change');

        $this->disableSearchSyncing();

        $entity->update(
            $this->getRequest('update')->all()
        );

        $this->syncProductMedia($entity, request('product_media', []));

        if ($shouldCreateRedirect && $newSlug && $oldSlug && $newSlug !== $oldSlug && (!$requestOldSlug || $requestOldSlug === $oldSlug)) {
            try {
                $baseSlug = setting('products_page_slug', 'products');
                $sourcePath = '/' . $baseSlug . '/' . ltrim($oldSlug, '/');
                $targetUrl = '/' . $baseSlug . '/' . ltrim($newSlug, '/');

                \Modules\Product\Entities\UrlRedirect::updateOrCreate(
                    ['source_path' => $sourcePath],
                    [
                        'target_type' => 'custom',
                        'target_id' => null,
                        'target_url' => $targetUrl,
                        'status_code' => 301,
                        'is_active' => true,
                    ]
                );
            } catch (\Throwable $e) {
            }
        }

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        $productEditResource = new ProductEditResource($entity);

        $this->searchable($entity);

        $message = trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]);

        if (request()->query('exit_flash')) {
            session()->flash('exit_flash', $message);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                    'product_resource' => $productEditResource,
                ],
                200
            );
        }
    }

    private function syncProductMedia(Product $product, array $payload): void
    {
        $keepIds = [];
        $position = 0;

        foreach ($payload as $row) {
            if (empty($row['path'])) {
                continue;
            }

            $ext = strtolower(pathinfo(parse_url($row['path'], PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $isVideo = in_array($ext, ['mp4','webm','ogg']);
            $poster = $row['poster'] ?? null;
            if ($isVideo && (!$poster || !is_string($poster))) {
                try {
                    $poster = media_variant_url($row, 400, 'webp') ?? media_variant_url($row, 400, 'jpg');
                } catch (\Throwable $e) {}
                if (!$poster) {
                    try {
                        $rawPath = parse_url($row['path'] ?? '', PHP_URL_PATH) ?? '';
                        $dir = dirname($rawPath);
                        $name = pathinfo($rawPath, PATHINFO_FILENAME);
                        $disk = config('filesystems.default');
                        $webpRel = $dir . '/' . $name . '-400w.webp';
                        $jpgRel = $dir . '/' . $name . '-400w.jpg';
                        if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($webpRel)) {
                            $poster = \Illuminate\Support\Facades\Storage::disk($disk)->url($webpRel);
                        } elseif (\Illuminate\Support\Facades\Storage::disk($disk)->exists($jpgRel)) {
                            $poster = \Illuminate\Support\Facades\Storage::disk($disk)->url($jpgRel);
                        }
                    } catch (\Throwable $e) {}
                }
                if (!$poster) {
                    if (!empty($row['variant_id'])) {
                        $variant = $product->variants()->withoutGlobalScope('active')->where('id', (int) $row['variant_id'])->first();
                        $poster = optional($variant?->base_image)->path ?: (optional($product->base_image)->path ?: asset('build/assets/image-placeholder.png'));
                    } else {
                        $poster = optional($product->base_image)->path ?: asset('build/assets/image-placeholder.png');
                    }
                }
            }

            $data = [
                'product_id' => $product->id,
                'variant_id' => $row['variant_id'] ?? null,
                'type' => $isVideo ? 'video' : 'image',
                'path' => $row['path'],
                'poster' => $poster,
                'position' => isset($row['position']) ? (int) $row['position'] : $position,
                'is_active' => 1,
            ];

            $position++;

            if (!empty($row['id'])) {
                $media = $product->productMedia()->where('id', (int) $row['id'])->first();
                if ($media) {
                    $media->update($data);
                    $keepIds[] = $media->id;
                    continue;
                }
            }

            $media = $product->productMedia()->create($data);
            $keepIds[] = $media->id;
        }

        if (!empty($keepIds)) {
            $product->productMedia()->whereNotIn('id', $keepIds)->delete();
        } else {
            // If empty payload, remove all existing media
            $product->productMedia()->delete();
        }

        $product->load('productMedia');
    }

    public function status($id)
    {
        $entity = $this->getEntity($id);

        $entity->update([
            'is_active' => request('is_active') ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
        ], 200);
    }


    public function updateBrand($id)
    {
        $entity = $this->getEntity($id);

        $brandId = request('brand_id');
        if ($brandId === '' || $brandId === null) {
            $brandId = null;
        } else {
            $brandId = (int) $brandId ?: null;
        }

        $entity->update([
            'brand_id' => $brandId,
        ]);

        return response()->json([
            'success' => true,
        ], 200);
    }


    public function updatePricing($id)
    {
        $entity = $this->getEntity($id);

        $payload = request()->all();

        $entity->setRelation('variants', $entity->variants()->withoutGlobalScope('active')->get());
        $wasInStock = (bool) $entity->isInStock();

        $currentPrice = $entity->price ? (float) $entity->price->amount() : null;
        $currentSpecial = $entity->hasSpecialPrice() ? (float) $entity->getSpecialPrice()->amount() : null;

        $productUpdate = [];
        if (array_key_exists('price', $payload) && is_numeric($payload['price'])) {
            $productUpdate['price'] = (float) $payload['price'];
        }
        if (array_key_exists('special_price', $payload) && $payload['special_price'] !== null && $payload['special_price'] !== '') {
            $sp = $payload['special_price'];
            $productUpdate['special_price'] = is_numeric($sp) ? (float) $sp : null;
        }

        $nextPrice = array_key_exists('price', $productUpdate) ? (float) $productUpdate['price'] : $currentPrice;
        $nextSpecial = array_key_exists('special_price', $productUpdate) ? $productUpdate['special_price'] : $currentSpecial;
        if ($nextSpecial !== null && $nextPrice !== null && (float) $nextSpecial > (float) $nextPrice) {
            return response()->json([
                'success' => false,
                'message' => 'İndirimli fiyat satış fiyatından yüksek olamaz.',
            ], 422);
        }
        if (!empty($productUpdate)) {
            $entity->update($productUpdate);
        }

        $wasLowStock = (bool) ($entity->qty < 3);

        if (array_key_exists('variants', $payload) && is_array($payload['variants'])) {
            foreach ($payload['variants'] as $vid => $attrs) {
                $variant = $entity->variants()->withoutGlobalScope('active')->where('id', $vid)->first();
                if ($variant) {
                    $vCurrentPrice = $variant->price ? (float) $variant->price->amount() : null;
                    $vCurrentSpecial = $variant->hasSpecialPrice() ? (float) $variant->getSpecialPrice()->amount() : null;

                    $update = [];
                    if (isset($attrs['price']) && is_numeric($attrs['price'])) {
                        $update['price'] = (float) $attrs['price'];
                    }
                    if (isset($attrs['special_price'])) {
                        $sp = $attrs['special_price'];
                        $update['special_price'] = ($sp === '' || $sp === null) ? null : (float) $sp;
                    }

                    $vNextPrice = array_key_exists('price', $update) ? (float) $update['price'] : $vCurrentPrice;
                    $vNextSpecial = array_key_exists('special_price', $update) ? $update['special_price'] : $vCurrentSpecial;
                    if ($vNextSpecial !== null && $vNextPrice !== null && (float) $vNextSpecial > (float) $vNextPrice) {
                        return response()->json([
                            'success' => false,
                            'message' => 'İndirimli fiyat satış fiyatından yüksek olamaz.',
                        ], 422);
                    }

                    if (!empty($update)) {
                        $variant->update($update);
                    }
                }
            }
        }

        $entity->refresh();

        $entity->setRelation('variants', $entity->variants()->withoutGlobalScope('active')->get());

        $isInStockNow = (bool) $entity->isInStock();
        $isLowStockNow = (bool) ($entity->qty < 3);

        if (! $wasInStock && $isInStockNow) {
            try {
                app(SendBackInStockNotifications::class)->handle($entity);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if (! $wasLowStock && $isLowStockNow) {
            try {
                $img = null;
                if ($entity->base_image && (int) ($entity->base_image->id ?? 0) > 0) {
                    $img = $entity->base_image->thumb_webp_url ?: ($entity->base_image->thumb_jpeg_url ?: $entity->base_image->url);
                }
                Mail::to(setting('store_email'))->send(new AdminStockAlertMail($entity, $entity->qty, null, $img, $entity->sku, []));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'success' => true,
        ], 200);
    }

    /**
     * Permanently delete resources by given ids (force delete) and detach relations.
     *
     * @param string $ids
     * @return JsonResponse
     */
    public function destroy(string $ids): JsonResponse
    {
        $idList = collect(explode(',', $ids))
            ->map(fn ($id) => (int) $id)
            ->filter();

        $deleted = [];

        foreach ($this->getModel()->withoutGlobalScope('active')->whereIn('id', $idList)->get() as $product) {
            try {
                // Detach pivot relations
                if (method_exists($product, 'categories')) {
                    $product->categories()->detach();
                }
                if (method_exists($product, 'tags')) {
                    $product->tags()->detach();
                }
                if (method_exists($product, 'options')) {
                    $product->options()->detach();
                }

                // Delete variants hard
                if (method_exists($product, 'variants')) {
                    $product->variants()->withoutGlobalScope('active')->forceDelete();
                }

                // Delete translations
                if (method_exists($product, 'translations')) {
                    $product->translations()->delete();
                }

                // Detach media
                if (method_exists($product, 'files')) {
                    $product->files()->detach();
                }

                $product->forceDelete();
                $deleted[] = $product->id;
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return response()->json([
            'success' => true,
            'message' => trans('admin::messages.resource_deleted', ['resource' => $this->getLabel()]),
            'ids' => $deleted,
        ], 200);
    }

    public function inventory($id)
    {
        $entity = $this->getEntity($id);
        $resource = new \Modules\Product\Transformers\ProductEditResource(
            $entity->load(['variants' => function ($q) {
                $q->withoutGlobalScope('active')->orderBy('position');
            }, 'saleUnit'])
        );

        return response()->json([
            'success' => true,
            'product' => $resource->toArray(request()),
        ], 200);
    }


    public function pricing($id)
    {
        $entity = $this->getEntity($id);
        $resource = new ProductEditResource(
            $entity->load(['variants' => function ($q) {
                $q->withoutGlobalScope('active')->orderBy('position');
            }])
        );

        return response()->json([
            'success' => true,
            'product' => $resource->toArray(request()),
        ], 200);
    }

    public function updateInventory($id)
    {
        $entity = $this->getEntity($id);

        $entity->setRelation('variants', $entity->variants()->withoutGlobalScope('active')->get());

        $wasInStock = (bool) $entity->isInStock();
        $wasLowStock = (bool) ($entity->qty < 3);

        $payload = request()->all();
        $allowDecimal = ($entity->saleUnit && (bool) $entity->saleUnit->is_decimal_stock);

        if (array_key_exists('qty', $payload) || array_key_exists('manage_stock', $payload) || array_key_exists('in_stock', $payload)) {
            $entity->withoutEvents(function () use ($entity, $payload, $allowDecimal) {
                $update = [];
                if (array_key_exists('qty', $payload)) {
                    $qty = $payload['qty'];
                    if (!$allowDecimal) {
                        $qty = is_numeric($qty) ? (int) floor((float) $qty) : 0;
                    }
                    $update['qty'] = $qty;
                    if (is_numeric($qty) && (float) $qty > 0 && !array_key_exists('in_stock', $payload)) {
                        $update['in_stock'] = 1;
                    }
                }
                if (array_key_exists('manage_stock', $payload)) {
                    $update['manage_stock'] = (bool) $payload['manage_stock'];
                }
                if (array_key_exists('in_stock', $payload)) {
                    $update['in_stock'] = (bool) $payload['in_stock'];
                }
                
                if (!empty($update)) {
                    $entity->update($update);
                }
            });
        }

        if (array_key_exists('variants', $payload) && is_array($payload['variants'])) {
            foreach ($payload['variants'] as $vid => $attrs) {
                $variant = $entity->variants()->withoutGlobalScope('active')->where('id', $vid)->first();
                if ($variant) {
                    $vWasInStock = (bool) $variant->isInStock();
                    $vWasLowStock = (bool) ($variant->qty < 3);

                    $update = [];
                    if (isset($attrs['qty'])) {
                        $vQty = $allowDecimal ? $attrs['qty'] : (is_numeric($attrs['qty']) ? (int) floor((float) $attrs['qty']) : 0);
                        $update['qty'] = $vQty;

                        if (is_numeric($vQty) && (float) $vQty > 0 && ! array_key_exists('in_stock', $attrs)) {
                            $update['in_stock'] = 1;
                        }
                    }
                    if (isset($attrs['in_stock'])) $update['in_stock'] = $attrs['in_stock'] ? 1 : 0;
                    if (isset($attrs['manage_stock'])) $update['manage_stock'] = $attrs['manage_stock'] ? 1 : 0;

                    if (!empty($update)) {
                        $variant->update($update);
                        
                        $variant->refresh();
                        if ($variant->manage_stock && ! $vWasLowStock && $variant->qty < 3) {
                            try {
                                $img = null;
                                if ($variant->base_image && (int) ($variant->base_image->id ?? 0) > 0) {
                                    $img = $variant->base_image->thumb_webp_url ?: ($variant->base_image->thumb_jpeg_url ?: $variant->base_image->url);
                                }
                                if (!$img && $entity->base_image && (int) ($entity->base_image->id ?? 0) > 0) {
                                    $img = $entity->base_image->thumb_webp_url ?: ($entity->base_image->thumb_jpeg_url ?: $entity->base_image->url);
                                }
                                Mail::to(setting('store_email'))->send(new AdminStockAlertMail($entity, $variant->qty, $variant->name, $img, $variant->sku ?: $entity->sku, $variant->getVariationLabels()->toArray()));
                            } catch (\Throwable $e) {
                                report($e);
                            }
                        }
                    }
                }
            }

            
        }

        $entity->refresh();

        $entity->setRelation('variants', $entity->variants()->withoutGlobalScope('active')->get());

        $isInStockNow = (bool) $entity->isInStock();
        $isLowStockNow = (bool) ($entity->qty < 3);

        if (! $wasInStock && $isInStockNow) {
            try {
                app(SendBackInStockNotifications::class)->handle($entity);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if ($entity->manage_stock && ! $wasLowStock && $isLowStockNow) {
            try {
                $img = null;
                if ($entity->base_image && (int) ($entity->base_image->id ?? 0) > 0) {
                    $img = $entity->base_image->thumb_webp_url ?: ($entity->base_image->thumb_jpeg_url ?: $entity->base_image->url);
                }
                Mail::to(setting('store_email'))->send(new AdminStockAlertMail($entity, $entity->qty, null, $img, $entity->sku, []));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'success' => true,
        ], 200);
    }

    /**
     * Duplicate the specified product and redirect to its edit page.
     *
     * @param int $id
     * @param ProductDuplicator $duplicator
     *
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function duplicate($id, ProductDuplicator $duplicator)
    {
        $product = $this->getEntity($id);

        $newProduct = $duplicator->duplicate($product);

        $message = trans('admin::messages.resource_created', ['resource' => trans('product::products.product')]);

        $redirectUrl = route('admin.products.edit', $newProduct->id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'new_id' => $newProduct->id,
                'redirect_url' => $redirectUrl,
            ], 200);
        }

        return redirect()->to($redirectUrl)->withSuccess($message);
    }



    public function productsForSorting($categoryId)
    {
        $category = \Modules\Category\Entities\Category::withoutGlobalScope('active')->findOrFail($categoryId);

        $products = $category->products()
            ->withoutGlobalScope('active')
            ->with(['files' => function($q) {
                $q->wherePivot('zone', 'base_image');
            }])
            ->orderByRaw('product_categories.position IS NULL, product_categories.position ASC')
            ->limit(400)
            ->get(['products.id', 'products.slug'])
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'image' => optional($product->base_image)->path,
                    'position' => $product->pivot->position,
                ];
            });

        return response()->json([
            'products' => $products,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ]);
    }

    public function saveProductOrder($categoryId)
    {
        $orderedIds = request('ordered_product_ids', []);

        if (empty($orderedIds) || !is_array($orderedIds)) {
            return response()->json(['message' => 'Invalid product order'], 400);
        }

        // Check if this is main page sorting (categoryId = 0 or null)
        $isMainPage = !$categoryId || $categoryId === '0' || $categoryId === 0;

        if ($isMainPage) {
            // Main page sorting: Update main_page_position column directly
            \DB::transaction(function() use ($orderedIds) {
                foreach ($orderedIds as $index => $productId) {
                    Product::withoutGlobalScope('active')
                        ->where('id', (int) $productId)
                        ->update(['main_page_position' => $index + 1]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Ana sayfa sıralaması kaydedildi.'
            ]);
        }

        // Specific category sorting
        $category = \Modules\Category\Entities\Category::withoutGlobalScope('active')->findOrFail($categoryId);

        \DB::transaction(function() use ($category, $orderedIds) {
            foreach ($orderedIds as $index => $productId) {
                \DB::table('product_categories')
                    ->where('category_id', $category->id)
                    ->where('product_id', (int) $productId)
                    ->update(['position' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Sıralama kaydedildi'
        ]);
    }

    public function resetProductOrder($categoryId)
    {
        $category = \Modules\Category\Entities\Category::withoutGlobalScope('active')->findOrFail($categoryId);

        \DB::table('product_categories')
            ->where('category_id', $category->id)
            ->update(['position' => null]);

        return response()->json(['message' => 'Sıralama sıfırlandı']);
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
            'is_active' => 'required|boolean',
        ]);

        $productIds = $request->input('product_ids');
        $isActive = $request->input('is_active');

        Product::withoutGlobalScope('active')
            ->whereIn('id', $productIds)
            ->update(['is_active' => $isActive]);

        $status = $isActive ? 'aktif' : 'pasif';
        $message = count($productIds) . " ürün {$status} yapıldı";

        return response()->json(['message' => $message]);
    }

    public function bulkUpdatePrice(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
            'price' => 'required|numeric|min:0',
        ]);

        $productIds = $request->input('product_ids');
        $price = $request->input('price');

        $invalidIds = Product::withoutGlobalScope('active')
            ->whereIn('id', $productIds)
            ->whereNotNull('special_price')
            ->where(function ($q) use ($price) {
                $q->whereNull('special_price_type')->orWhere('special_price_type', '!=', 'percent');
            })
            ->where('special_price', '>', (float) $price)
            ->pluck('id')
            ->all();

        if (!empty($invalidIds)) {
            $sample = array_slice($invalidIds, 0, 10);
            $more = count($invalidIds) > count($sample) ? '…' : '';

            return response()->json([
                'message' => 'Satış fiyatı, mevcut indirimli fiyattan düşük olamaz. Önce indirimli fiyatı düşürün/kaldırın. Hatalı ürünler: ' . implode(', ', $sample) . $more,
                'invalid_ids' => $invalidIds,
            ], 422);
        }

        Product::withoutGlobalScope('active')
            ->whereIn('id', $productIds)
            ->update(['price' => $price]);

        $message = count($productIds) . " ürünün satış fiyatı güncellendi";

        return response()->json(['message' => $message]);
    }

    public function bulkUpdateSpecialPrice(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
            'special_price' => 'nullable|numeric|min:0',
        ]);

        $productIds = $request->input('product_ids');
        $specialPrice = $request->input('special_price');

        if ($specialPrice !== null && $specialPrice !== '') {
            $specialPrice = (float) $specialPrice;

            $invalidIds = Product::withoutGlobalScope('active')
                ->whereIn('id', $productIds)
                ->whereNotNull('price')
                ->where('price', '<', $specialPrice)
                ->pluck('id')
                ->all();

            if (!empty($invalidIds)) {
                $sample = array_slice($invalidIds, 0, 10);
                $more = count($invalidIds) > count($sample) ? '…' : '';

                return response()->json([
                    'message' => 'İndirimli fiyat satış fiyatından yüksek olamaz. Hatalı ürünler: ' . implode(', ', $sample) . $more,
                    'invalid_ids' => $invalidIds,
                ], 422);
            }
        }

        Product::withoutGlobalScope('active')
            ->whereIn('id', $productIds)
            ->update(['special_price' => $specialPrice]);

        if ($specialPrice === null) {
            $message = count($productIds) . " ürünün indirimli fiyatı kaldırıldı";
        } else {
            $message = count($productIds) . " ürünün indirimli fiyatı güncellendi";
        }

        return response()->json(['message' => $message]);
    }

    public function bulkUpdateStock(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
            'stock' => 'required|integer|min:0',
        ]);

        $productIds = $request->input('product_ids');
        $stock = $request->input('stock');

        Product::withoutGlobalScope('active')
            ->whereIn('id', $productIds)
            ->update(['qty' => $stock]);

        $message = count($productIds) . " ürünün stok miktarı güncellendi";

        return response()->json(['message' => $message]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $productIds = $request->input('product_ids');

        DB::transaction(function () use ($productIds) {
            Product::withoutGlobalScope('active')
                ->whereIn('id', $productIds)
                ->delete();
        });

        $message = count($productIds) . " ürün silindi";

        return response()->json(['message' => $message]);
    }

    public function bulkPreview(Request $request): JsonResponse
    {
        $filters = $request->input('filters', []);
        $productIds = $request->input('product_ids', []);
        $actions = $request->input('actions', []);
        $combine = strtolower($request->input('combine', 'and')) === 'or' ? 'or' : 'and';

        $query = Product::query()
            ->withoutGlobalScope('active')
            ->withName()
            ->with(['primaryCategory', 'brand', 'variants', 'translations', 'categories', 'tags'])
            ->withBaseImage();

        if (!empty($productIds) && is_array($productIds)) {
            $query->whereIn('products.id', array_map('intval', $productIds));
        }

        $this->applyBulkFilters($query, $filters, $combine);

        $total = (clone $query)->count();

        $invalidSpecialPrice = [];

        $items = $query
            ->limit(20)
            ->get()
            ->map(function (Product $product) use ($actions, &$invalidSpecialPrice) {
                $simulatedValues = []; 
                $changes = [];

                foreach ($actions as $action) {
                    $attr = $action['attribute'] ?? null;
                    $mode = $action['mode'] ?? 'set';
                    $val = $action['value'] ?? null;

                    if (!$attr) continue;

                    $label = $this->getAttributeLabel($attr);
                    $original = null;
                    $new = null;

                    if ($attr === 'price' || $attr === 'special_price') {
                        $rawOrig = ($attr === 'price' ? $product->price : ($product->hasSpecialPrice() ? $product->getSpecialPrice() : null));
                        $current = $simulatedValues[$attr] ?? ($rawOrig ? (float)$rawOrig->amount() : null);
                        $original = $original ?? ($current !== null ? number_format($current, 2, '.', '') : '-');
                        
                        if ($mode === 'set') $new = $val;
                        elseif ($mode === 'increase_percent') $new = $current + ($current * ($val / 100));
                        elseif ($mode === 'decrease_percent') $new = $current - ($current * ($val / 100));
                        elseif ($mode === 'increase_fixed') $new = $current + $val;
                        elseif ($mode === 'decrease_fixed') $new = $current - $val;
                        elseif ($mode === 'clear') $new = null;

                        if ($new !== null) {
                            $new = number_format((float)$new, 2, '.', '');
                            $simulatedValues[$attr] = (float)$new;
                        } else {
                            $simulatedValues[$attr] = null;
                            $new = 'Kaldırıldı';
                        }
                    } elseif ($attr === 'qty') {
                        $current = $simulatedValues[$attr] ?? (float)$product->qty;
                        $allowDecimal = (bool) $product->unit_decimal;
                        
                        $formatStock = function($q) use ($allowDecimal) {
                             if (!$allowDecimal) return (string) (int) $q;
                             return fmod($q, 1) === 0.0 ? (string)(int)$q : rtrim(rtrim(number_format($q, 2, '.', ''), '0'), '.');
                        };

                        $original = $original ?? $formatStock($current);

                        if ($mode === 'set') $new = (float)$val;
                        elseif ($mode === 'increase') $new = (float)$current + (float)$val;
                        elseif ($mode === 'decrease') $new = (float)$current - (float)$val;

                        if (!$allowDecimal) {
                            $new = floor($new);
                        }

                        $simulatedValues[$attr] = max(0, $new);
                        $new = $formatStock($simulatedValues[$attr]);
                    } elseif ($attr === 'manage_stock') {
                        $original = $product->manage_stock ? 'Takip Yapılıyor' : 'Takip Yok';
                        $new = (int)$val === 1 ? 'Takip Yapılıyor' : 'Takip Yok';
                        $simulatedValues[$attr] = (int)$val;
                    } elseif ($attr === 'in_stock') {
                        $original = $product->in_stock ? 'Stokta' : 'Stokta Yok';
                        $new = (int)$val === 1 ? 'Stokta' : 'Stokta Yok';
                        $simulatedValues[$attr] = (int)$val;
                    } elseif ($attr === 'is_active') {
                        $original = $product->is_active ? 'Yayında' : 'Yayında Değil';
                        $new = (int)$val === 1 ? 'Yayında' : 'Yayında Değil';
                        $simulatedValues[$attr] = (int)$val;
                    } elseif ($attr === 'brand_id') {
                        $original = optional($product->brand)->name ?? 'Yok';
                        $brand = \Modules\Brand\Entities\Brand::find((int)$val);
                        $new = $brand ? $brand->name : ($val === null ? 'Yok' : '#' . $val);
                    } elseif ($attr === 'tax_class_id') {
                        $original = optional($product->taxClass)->label ?? 'Yok';
                        $tax = \Modules\Tax\Entities\TaxClass::find((int)$val);
                        $new = $tax ? $tax->label : ($val === null ? 'Yok' : '#' . $val);
                    } elseif ($attr === 'primary_category') {
                        $original = optional($product->primaryCategory)->name ?? 'Yok';
                        $cat = \Modules\Category\Entities\Category::find((int)$val);
                        $new = $cat ? $cat->name : ($val === null ? 'Yok' : '#' . $val);
                    } elseif ($attr === 'name' || $attr === 'sku') {
                        $current = $simulatedValues[$attr] ?? $product->{$attr};
                        $original = $original ?? $current;
                        if ($mode === 'set') $new = $val;
                        elseif ($mode === 'prefix') $new = $val . $current;
                        elseif ($mode === 'suffix') $new = $current . $val;
                        elseif ($mode === 'search_replace' && is_array($val)) {
                            $new = str_replace($val['search'], $val['replace'], $current);
                        }
                        $simulatedValues[$attr] = $new;
                    }

                    if ($new !== null && $original != $new) {
                        $changes[] = [
                            'label' => $label,
                            'original' => (string)$original,
                            'new' => (string)$new
                        ];
                    }
                }

                $warnings = [];
                $finalPrice = $simulatedValues['price'] ?? ($product->price ? (float)$product->price->amount() : 0);
                $finalSpecial = $simulatedValues['special_price'] ?? ($product->hasSpecialPrice() ? (float)$product->getSpecialPrice()->amount() : null);

                if ($finalSpecial !== null && $finalSpecial > $finalPrice) {
                    $warnings[] = "İndirimli fiyat ({$finalSpecial}), normal fiyattan ({$finalPrice}) büyük olamaz!";
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'thumbnail' => $product->base_image->path ?? null,
                    'changes' => $changes,
                    'warnings' => $warnings
                ];
            });

        return response()->json([
            'total' => $total,
            'items' => $items,
            'invalid_special_price' => $invalidSpecialPrice
        ]);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $filters = $request->input('filters', []);
        $productIds = $request->input('product_ids', []);
        $actions = $request->input('actions', []);
        $combine = strtolower($request->input('combine', 'and')) === 'or' ? 'or' : 'and';
        $applyToVariants = (bool)$request->input('apply_to_variants', true);

        $query = Product::query()
            ->withoutGlobalScope('active')
            ->with(['saleUnit', 'variants', 'categories', 'tags']);

        if (!empty($productIds) && is_array($productIds)) {
            $query->whereIn('id', array_map('intval', $productIds));
        }

        $this->applyBulkFilters($query, $filters, $combine);

        $count = 0;
        $query->chunk(100, function ($products) use ($actions, &$count, $applyToVariants) {
            foreach ($products as $product) {
                foreach ($actions as $action) {
                    $attr = $action['attribute'] ?? null;
                    $mode = $action['mode'] ?? 'set';
                    $val = $action['value'] ?? null;
                    if (!$attr) continue;

                    try {
                        if ($attr === 'price' || $attr === 'special_price' || $attr === 'qty') {
                            $current = 0;
                            if ($attr === 'qty') {
                                $current = (float)$product->qty;
                            } elseif ($attr === 'price') {
                                $current = $product->price ? $product->price->amount() : 0;
                            } elseif ($attr === 'special_price') {
                                $current = $product->hasSpecialPrice() ? $product->getSpecialPrice()->amount() : 0;
                            }
                            
                            $new = (float)$current;
                            if ($mode === 'set') $new = (float)$val;
                            elseif ($mode === 'increase_percent') $new = $current + ($current * ((float)$val / 100));
                            elseif ($mode === 'decrease_percent') $new = $current - ($current * ((float)$val / 100));
                            elseif ($mode === 'increase_fixed' || $mode === 'increase') $new = $current + (float)$val;
                            elseif ($mode === 'decrease_fixed' || $mode === 'decrease') $new = $current - (float)$val;
                            elseif ($mode === 'clear') $new = null;

                            if ($attr === 'qty') {
                                if ($product->saleUnit && !$product->saleUnit->isDecimalStock()) {
                                    $new = floor($new);
                                }
                                $updateData = ['qty' => max(0, (float)$new)];
                                $product->update($updateData);
                                if ($applyToVariants) $product->variants()->update($updateData);
                            } else {
                                $updateData = [$attr => $new];
                                $product->update($updateData);
                                
                                // Validation for price vs special_price
                                $pObj = $product->fresh();
                                $regPrice = $pObj->price ? $pObj->price->amount() : 0;
                                $specPrice = ($pObj->special_price !== null) ? (float)$pObj->special_price : null;

                                if ($specPrice !== null && $specPrice > $regPrice) {
                                    $pObj->update(['special_price' => $regPrice]);
                                }

                                if ($applyToVariants) {
                                    foreach ($pObj->variants as $variant) {
                                        $variant->update($updateData);
                                        $vReg = $variant->price ? $variant->price->amount() : 0;
                                        $vSpec = ($variant->special_price !== null) ? (float)$variant->special_price : null;
                                        if ($vSpec !== null && $vSpec > $vReg) {
                                            $variant->update(['special_price' => $vReg]);
                                        }
                                    }
                                }
                            }
                        } elseif (in_array($attr, ['is_active', 'manage_stock', 'in_stock', 'brand_id', 'tax_class_id', 'primary_category'])) {
                            $column = ($attr === 'primary_category') ? 'primary_category_id' : $attr;
                            $updateData = [$column => $val];
                            $product->update($updateData);

                            // For primary category, also ensure it's in the pivot table
                            if ($attr === 'primary_category' && !empty($val)) {
                                $product->categories()->syncWithoutDetaching([$val]);
                            }

                            // Sync only applicable fields to variants
                            if ($applyToVariants && in_array($attr, ['is_active', 'manage_stock', 'in_stock'])) {
                                $product->variants()->update($updateData);
                            }
                        } elseif ($attr === 'category_action') {
                            if ($mode === 'add') $product->categories()->syncWithoutDetaching((array)$val);
                            elseif ($mode === 'remove') $product->categories()->detach((array)$val);
                        } elseif (in_array($attr, ['name', 'sku', 'short_description', 'description'])) {
                            $current = (string)$product->{$attr};
                            $new = $current;
                            if ($mode === 'set') $new = $val;
                            elseif ($mode === 'prefix') $new = (string)$val . $current;
                            elseif ($mode === 'suffix') $new = $current . (string)$val;
                            elseif ($mode === 'search_replace' && is_array($val)) {
                                $new = str_replace($val['search'] ?? '', $val['replace'] ?? '', $current);
                            }
                            $product->update([$attr => $new]);
                            // Translatable fields don't usually sync to variants automatically in FleetCart
                        }
                    } catch (\Exception $e) {
                        \Log::error("Bulk Update Error [Product ID: {$product->id}]: " . $e->getMessage());
                        continue;
                    }

                }
                $count++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "{$count} ürün başarıyla güncellendi."
        ]);
    }

    private function getAttributeLabel($attr): string
    {
        $labels = [
            'name' => 'Ürün Adı',
            'sku' => 'SKU',
            'price' => 'Satış Fiyatı',
            'special_price' => 'İndirimli Fiyat',
            'qty' => 'Stok Miktarı',
            'is_active' => 'Yayın Durumu',
            'manage_stock' => 'Stok Takibi',
            'in_stock' => 'Stok Durumu',
            'brand_id' => 'Marka',
            'primary_category' => 'Ana Kategori',
            'category_action' => 'Kategori',
            'tax_class_id' => 'Vergi Sınıfı',
            'description' => 'Açıklama',
            'short_description' => 'Kısa Açıklama'
        ];
        return $labels[$attr] ?? $attr;
    }

    private function applyBulkFilters($query, $filters, $combine = 'and')
    {
        if (empty($filters)) return;

        $query->where(function ($q) use ($filters, $combine) {
            foreach ($filters as $index => $filter) {
                $attr = $filter['attribute'] ?? null;
                $operator = $filter['operator'] ?? '=';
                $value = $filter['value'] ?? null;

                if (!$attr) continue;

                $whereFunc = ($combine === 'or' && $index > 0) ? 'orWhere' : 'where';

                if ($attr === 'category_id') {
                    $q->{$whereFunc . 'Has'}('categories', function ($catQ) use ($value) {
                        $catQ->whereIn('categories.id', (array)$value);
                    });
                } elseif ($attr === 'price' || $attr === 'qty') {
                    $q->{$whereFunc}($attr, $operator, $value);
                } elseif ($attr === 'is_active' || $attr === 'brand_id') {
                    $q->{$whereFunc}($attr, '=', $value);
                } else {
                    $q->{$whereFunc}($attr, 'like', "%{$value}%");
                }
            }
        });
    }

}

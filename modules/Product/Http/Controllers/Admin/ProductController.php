<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Modules\Product\Entities\Product;
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

class ProductController
{
    use HasCrudActions;

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
        if ($request->has('query')) {
            return $this->getModel()
                ->search($request->get('query'))
                ->query()
                ->limit($request->get('limit', 10))
                ->get();
        }

        $brands = Brand::list();
        $categories = Category::treeList();

        return view("{$this->viewPath}.index", compact('brands', 'categories'));
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
                $sourcePath = '/products/' . ltrim($oldSlug, '/');
                $targetUrl = '/products/' . ltrim($newSlug, '/');

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

        $productUpdate = [];
        if (array_key_exists('price', $payload) && is_numeric($payload['price'])) {
            $productUpdate['price'] = (float) $payload['price'];
        }
        if (array_key_exists('special_price', $payload) && $payload['special_price'] !== null && $payload['special_price'] !== '') {
            $sp = $payload['special_price'];
            $productUpdate['special_price'] = is_numeric($sp) ? (float) $sp : null;
        }
        if (!empty($productUpdate)) {
            $entity->update($productUpdate);
        }

        if (array_key_exists('variants', $payload) && is_array($payload['variants'])) {
            foreach ($payload['variants'] as $vid => $attrs) {
                $variant = $entity->variants()->withoutGlobalScope('active')->where('id', $vid)->first();
                if ($variant) {
                    $update = [];
                    if (isset($attrs['price']) && is_numeric($attrs['price'])) {
                        $update['price'] = (float) $attrs['price'];
                    }
                    if (isset($attrs['special_price'])) {
                        $sp = $attrs['special_price'];
                        $update['special_price'] = ($sp === '' || $sp === null) ? null : (float) $sp;
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

        if (! $wasInStock && $isInStockNow) {
            try {
                app(SendBackInStockNotifications::class)->handle($entity);
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

        $payload = request()->all();
        $allowDecimal = ($entity->saleUnit && (bool) $entity->saleUnit->is_decimal_stock);

        if (array_key_exists('qty', $payload)) {
            $qty = $payload['qty'];
            if (!$allowDecimal) {
                $qty = is_numeric($qty) ? (int) floor((float) $qty) : 0;
            }
            $entity->withoutEvents(function () use ($entity, $qty) {
                $update = ['qty' => $qty];

                if (is_numeric($qty) && (float) $qty > 0) {
                    $update['in_stock'] = 1;
                }

                $entity->update($update);
            });
        }

        if (array_key_exists('variants', $payload) && is_array($payload['variants'])) {
            foreach ($payload['variants'] as $vid => $attrs) {
                $variant = $entity->variants()->withoutGlobalScope('active')->where('id', $vid)->first();
                if ($variant) {
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
                    }
                }
            }

            
        }

        $entity->refresh();

        $entity->setRelation('variants', $entity->variants()->withoutGlobalScope('active')->get());

        $isInStockNow = (bool) $entity->isInStock();

        if (! $wasInStock && $isInStockNow) {
            try {
                app(SendBackInStockNotifications::class)->handle($entity);
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


    public function bulkEditor(): Factory|View|Application
    {
        $brands = Brand::list();
        $categories = Category::treeList();

        // Use the same treeList output as a flat id => name array for selects
        $flatCategories = $categories;

        $categoryTree = Category::query()
            ->orderBy('position')
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => (string) $cat->id,
                    'parent' => $cat->parent_id ? (string) $cat->parent_id : '#',
                    'text' => $cat->name,
                ];
            });

        return view('product::admin.products.bulk_editor', compact('brands', 'categories', 'flatCategories', 'categoryTree'));
    }


    public function bulkPreview(Request $request): JsonResponse
    {
        $filters = $request->input('filters', []);
        $combine = strtolower($request->input('combine', 'and')) === 'or' ? 'or' : 'and';

        $query = Product::query()
            ->withoutGlobalScope('active')
            ->with(['primaryCategory', 'brand'])
            ->withBaseImage();

        $this->applyBulkFilters($query, $filters, $combine);

        $total = (clone $query)->count();

        $items = $query
            ->limit(50)
            ->get()
            ->map(function (Product $product) {
                $rawPrice = $product->getAttribute('price');
                $rawSpecial = $product->getAttribute('special_price');

                $price = null;
                $priceFormatted = null;
                if ($rawPrice instanceof \Modules\Support\Money) {
                    $price = $rawPrice->amount();
                    $priceFormatted = $rawPrice->format();
                } elseif (is_numeric($rawPrice)) {
                    $price = (float) $rawPrice;
                    try {
                        $priceFormatted = \Modules\Support\Money::inDefaultCurrency($price)->format();
                    } catch (\Throwable $e) {
                        $priceFormatted = number_format($price, 2);
                    }
                }

                $special = null;
                $specialFormatted = null;
                if ($rawSpecial instanceof \Modules\Support\Money) {
                    $special = $rawSpecial->amount();
                    $specialFormatted = $rawSpecial->format();
                } elseif ($rawSpecial !== null && $rawSpecial !== '' && is_numeric($rawSpecial)) {
                    $special = (float) $rawSpecial;
                    try {
                        $specialFormatted = \Modules\Support\Money::inDefaultCurrency($special)->format();
                    } catch (\Throwable $e) {
                        $specialFormatted = number_format($special, 2);
                    }
                }

                $category = $product->primaryCategory;
                $categoryName = '';
                if ($category) {
                    $categoryName = $category->name;

                    if ($categoryName === null || $categoryName === '') {
                        try {
                            $translation = $category->translations()
                                ->withoutGlobalScope('locale')
                                ->first();
                            if ($translation && isset($translation->name)) {
                                $categoryName = $translation->name;
                            }
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    }
                }

                // Resolve a simple image URL from base_image accessor (if available)
                $imageUrl = null;
                $baseImage = $product->base_image ?? null;

                if ($baseImage) {
                    $path = null;

                    if (is_array($baseImage)) {
                        $path = $baseImage['path'] ?? null;
                    } elseif (is_object($baseImage)) {
                        // Typical ProductMedia model instance
                        $path = $baseImage->path ?? null;
                    }

                    if (is_string($path) && $path !== '') {
                        if (preg_match('#^https?://#', $path)) {
                            $imageUrl = $path;
                        } else {
                            if (str_starts_with($path, '/')) {
                                $imageUrl = url($path);
                            } elseif (str_starts_with($path, 'storage/')) {
                                $imageUrl = url('/' . $path);
                            } else {
                                $imageUrl = url('/storage/' . $path);
                            }
                        }
                    }
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => optional($product->brand)->name,
                    'category' => $categoryName,
                    'price' => $price,
                    'price_formatted' => $priceFormatted,
                    'special_price' => $special,
                    'special_price_formatted' => $specialFormatted,
                    'image' => $imageUrl,
                ];
            });

        return response()->json([
            'success' => true,
            'total' => $total,
            'items' => $items,
        ], 200);
    }


    public function bulkUpdate(Request $request): JsonResponse
    {
        $filters = $request->input('filters', []);
        $combine = strtolower($request->input('combine', 'and')) === 'or' ? 'or' : 'and';
        $actions = $request->input('actions', []);

        if (empty($actions) || !is_array($actions)) {
            return response()->json([
                'success' => false,
                'message' => 'No update actions provided.',
            ], 422);
        }

        $query = Product::query()
            ->withoutGlobalScope('active')
            ->with(['variants' => function ($q) {
                $q->withoutGlobalScope('active');
            }]);

        $this->applyBulkFilters($query, $filters, $combine);

        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No products matched the given filters.',
            ], 422);
        }

        foreach ($products as $product) {
            $baseUpdates = [];

            foreach ($actions as $action) {
                $attribute = $action['attribute'] ?? null;
                $mode = $action['mode'] ?? null;
                $value = $action['value'] ?? null;

                if ($attribute === 'price') {
                    $rawCurrent = $product->getAttribute('price');

                    if ($rawCurrent instanceof \Modules\Support\Money) {
                        $current = $rawCurrent->amount();
                    } elseif (is_numeric($rawCurrent)) {
                        $current = (float) $rawCurrent;
                    } else {
                        $current = 0.0;
                    }

                    if ($mode === 'set' && is_numeric($value)) {
                        $newPrice = (float) $value;
                    } elseif (($mode === 'increase_percent' || $mode === 'decrease_percent') && is_numeric($value)) {
                        $percent = (float) $value;
                        $delta = $current * ($percent / 100);
                        $newPrice = $mode === 'increase_percent' ? $current + $delta : $current - $delta;
                    } else {
                        continue;
                    }

                    if ($newPrice < 0) {
                        $newPrice = 0;
                    }

                    $baseUpdates['price'] = $newPrice;

                    foreach ($product->variants as $variant) {
                        $rawVariantPrice = $variant->getAttribute('price');
                        if ($rawVariantPrice instanceof \Modules\Support\Money) {
                            $variantCurrent = $rawVariantPrice->amount();
                        } elseif (is_numeric($rawVariantPrice)) {
                            $variantCurrent = (float) $rawVariantPrice;
                        } else {
                            $variantCurrent = 0.0;
                        }

                        $variantNew = $variantCurrent;

                        if ($mode === 'set' && is_numeric($value)) {
                            $variantNew = (float) $value;
                        } elseif (($mode === 'increase_percent' || $mode === 'decrease_percent') && is_numeric($value)) {
                            $deltaV = $variantCurrent * ((float) $value / 100);
                            $variantNew = $mode === 'increase_percent' ? $variantCurrent + $deltaV : $variantCurrent - $deltaV;
                        }

                        if ($variantNew < 0) {
                            $variantNew = 0;
                        }

                        $variant->update(['price' => $variantNew]);
                    }
                }

                if ($attribute === 'special_price') {
                    if ($mode === 'set' && is_numeric($value)) {
                        $sp = (float) $value;
                        $baseUpdates['special_price'] = $sp;

                        foreach ($product->variants as $variant) {
                            $variant->update(['special_price' => $sp]);
                        }
                    } elseif ($mode === 'clear') {
                        $baseUpdates['special_price'] = null;

                        foreach ($product->variants as $variant) {
                            $variant->update(['special_price' => null]);
                        }
                    }
                }

                if ($attribute === 'primary_category') {
                    if ($mode === 'set' && $value) {
                        $newCategoryId = (int) $value;
                        if ($newCategoryId > 0) {
                            $baseUpdates['primary_category_id'] = $newCategoryId;

                            // Ensure pivot relation exists so SEO/category helpers behave correctly
                            try {
                                $product->categories()->syncWithoutDetaching([$newCategoryId]);
                            } catch (\Throwable $e) {
                                // ignore pivot sync errors in bulk context
                            }
                        }
                    }
                }

                if ($attribute === 'short_description') {
                    if ($mode === 'set' && is_string($value) && $value !== '') {
                        foreach ($product->translations as $translation) {
                            $translation->short_description = $value;
                            $translation->save();
                        }
                    }
                }
            }

            if (!empty($baseUpdates)) {
                $product->update($baseUpdates);
            }
        }

        return response()->json([
            'success' => true,
            'updated' => $products->count(),
        ], 200);
    }


    protected function applyBulkFilters($query, array $filters, string $combine): void
    {
        if (empty($filters)) {
            return;
        }

        $boolean = $combine === 'or' ? 'or' : 'and';

        $query->where(function ($outer) use ($filters, $boolean) {
            foreach ($filters as $index => $filter) {
                $attribute = $filter['attribute'] ?? null;
                $operator = $filter['operator'] ?? null;
                $value = $filter['value'] ?? null;

                if (!$attribute || $operator === null) {
                    continue;
                }

                $method = $index === 0 ? 'where' : ($boolean === 'or' ? 'orWhere' : 'where');

                if ($attribute === 'name') {
                    if (!is_string($value) || trim($value) === '') {
                        continue;
                    }
                    $outer->{$method}(function ($q) use ($operator, $value) {
                        if ($operator === 'contains') {
                            $q->whereHas('translations', function ($t) use ($value) {
                                $t->where('name', 'like', '%' . $value . '%');
                            });
                        }
                    });
                } elseif ($attribute === 'brand') {
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $outer->{$method}(function ($q) use ($operator, $value) {
                        if ($operator === '=') {
                            $q->where('brand_id', (int) $value);
                        }
                    });
                } elseif ($attribute === 'category') {
                    $ids = is_array($value) ? $value : [$value];
                    $ids = array_filter(array_map('intval', $ids));

                    if (empty($ids)) {
                        continue;
                    }

                    $outer->{$method}(function ($q) use ($ids) {
                        $q->where(function ($sub) use ($ids) {
                            $sub->whereIn('primary_category_id', $ids)
                                ->orWhereHas('categories', function ($cat) use ($ids) {
                                    $cat->whereIn('categories.id', $ids);
                                });
                        });
                    });
                } elseif ($attribute === 'price') {
                    if (!is_numeric($value)) {
                        continue;
                    }
                    $v = (float) $value;

                    $outer->{$method}(function ($q) use ($operator, $v) {
                        if ($operator === '>=') {
                            $q->where('price', '>=', $v);
                        } elseif ($operator === '<=') {
                            $q->where('price', '<=', $v);
                        } elseif ($operator === '>') {
                            $q->where('price', '>', $v);
                        } elseif ($operator === '<') {
                            $q->where('price', '<', $v);
                        } elseif ($operator === '=') {
                            $q->where('price', '=', $v);
                        }
                    });
                }
            }
        });
    }
}

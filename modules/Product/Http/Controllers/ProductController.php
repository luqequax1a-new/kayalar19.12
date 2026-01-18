<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Modules\Tag\Entities\Tag;
use Modules\Tag\Entities\TagBadge;
use Modules\Review\Entities\Review;
use Modules\Product\Entities\ProductVariant;
use Modules\Product\Entities\Product;
use Illuminate\Contracts\View\Factory;
use Modules\Product\Events\ProductViewed;
use Modules\Product\Filters\ProductFilter;
use Illuminate\Contracts\Foundation\Application;
use Modules\Product\Repositories\ProductRepository;
use Modules\Product\Http\Middleware\SetProductSortOption;
use Modules\Product\Entities\UrlRedirect;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Illuminate\Support\Str;
use Modules\Category\Entities\Category;
use Modules\DynamicCategory\Entities\DynamicCategory;
use Modules\DynamicCategory\Services\DynamicCategoryProductService;
use Modules\Product\Events\ShowingProductList;
use Modules\FlashSale\Entities\FlashSale;

class ProductController extends BaseController
{
    use ProductSearch;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(SetProductSortOption::class)->only('index');
    }


    /**
     * Display a listing of the resource.
     *
     * @param Product $model
     * @param ProductFilter $productFilter
     *
     * @return JsonResponse|Application|Factory|View
     */
    public function index(Product $model, ProductFilter $productFilter)
    {
        if (request()->expectsJson()) {
            $isLocalHost = in_array(request()->getHost(), ['127.0.0.1', 'localhost'], true);
            $shouldInstrument = $isLocalHost
                || (! app()->environment('production')
                    && ((bool) config('app.debug') || app()->environment(['local', 'development', 'dev', 'testing'])));

            $start = $shouldInstrument ? microtime(true) : null;
            $queryCount = 0;
            $dbTimeMs = 0.0;

            if ($shouldInstrument) {
                DB::listen(function ($query) use (&$queryCount, &$dbTimeMs) {
                    $queryCount++;
                    $dbTimeMs += (float) $query->time;
                });
            }

            $categorySlug = request('category');

            $response = null;

            if ($categorySlug) {
                $response = $this->searchProducts($model, $productFilter);
            }

            if ($response === null) {
                $response = $this->searchProducts($model, $productFilter);
            }

            return $response;
        }

        $payload = $this->buildListingPayload($model, $productFilter);

        $firstProduct = null;
        try {
            $p = $payload['products'] ?? null;
            if ($p && method_exists($p, 'first')) {
                $firstProduct = $p->first();
            } elseif ($p && method_exists($p, 'getCollection')) {
                $firstProduct = $p->getCollection()->first();
            }
        } catch (\Throwable $e) {
            $firstProduct = null;
        }

        return view('storefront::public.products.index', [
            'initialProducts' => $payload['products'] ?? null,
            'initialAttributes' => $payload['attributes'] ?? null,
            'initialBrands' => $payload['brands'] ?? null,
            'initialCategoryData' => $payload['category'] ?? null,
            'firstProduct' => $firstProduct,
            'category' => $this->resolvedCategory(),
            'categoryName' => $payload['category']['name'] ?? null,
            'categoryMetaTitle' => $payload['category']['meta_title'] ?? null,
            'categoryMetaDescription' => $payload['category']['meta_description'] ?? null,
        ]);
    }


    /**
     * Show the specified resource.
     *
     * @param string $slug
     *
     * @return Response
     */
    public function show($slug, \Modules\Cart\Services\CartUpsellService $upsellService)
    {
        $path = request()->getPathInfo();

        $segments = explode('/', ltrim($path, '/'));
        $supportedLocales = array_keys(LaravelLocalization::getSupportedLocales());
        if (!empty($segments) && in_array($segments[0], $supportedLocales)) {
            array_shift($segments);
        }
        $canonical = '/' . implode('/', $segments);

        $variantResolvedFromCleanSlug = false;

        try {
            $product = ProductRepository::findBySlug($slug);
        } catch (\Throwable $e) {
            // POC: Support clean variant URLs like:
            // /products/{product-slug}-{variant-slug}
            //
            // Parsing strategy (minimal risk): progressively split from the RIGHTMOST '-'
            // and try to resolve the left part as a product slug until we find a match.
            $candidateVariantSlug = null;
            $parsedProduct = null;

            if (is_string($slug) && Str::contains($slug, '-')) {
                $probe = $slug;

                while (($pos = strrpos($probe, '-')) !== false) {
                    $candidateProductSlug = substr($slug, 0, $pos);
                    $candidateVariantSlug = substr($slug, $pos + 1);

                    if (! is_string($candidateProductSlug) || $candidateProductSlug === '' || $candidateVariantSlug === '') {
                        break;
                    }

                    try {
                        $parsedProduct = ProductRepository::findBySlug($candidateProductSlug);
                        break;
                    } catch (\Throwable $ignored) {
                        // Move the split point leftwards (keep searching for a valid product slug)
                        $probe = $candidateProductSlug;
                        continue;
                    }
                }
            }

            if ($parsedProduct instanceof Product) {
                $product = $parsedProduct;

                if (is_string($candidateVariantSlug) && $candidateVariantSlug !== '') {
                    $candidateVariantSlug = Str::slug($candidateVariantSlug);

                    // Resolve the variant by computing a slug from its selected variation value labels.
                    // - variant->uids is a dot-separated list of variation value UIDs
                    // - Map each UID -> label via product variations/values
                    // - Slugify labels and join with '-'
                    $product->loadMissing(['variations.values']);

                    $valueLabelsByUid = [];
                    foreach ($product->variations as $variation) {
                        foreach ($variation->values as $value) {
                            $uid = (string) ($value->uid ?? '');
                            if ($uid !== '') {
                                $valueLabelsByUid[$uid] = (string) ($value->label ?? '');
                            }
                        }
                    }

                    $variants = $product->variants()->withoutGlobalScope('active')->get();

                    foreach ($variants as $variant) {
                        $valueUids = array_filter(explode('.', (string) $variant->uids));
                        $valueUidSet = array_fill_keys($valueUids, true);

                        // Ordering MUST match storefront JS: use product variations order as displayed,
                        // and for each variation take the selected value label that belongs to this variant.
                        $parts = [];
                        foreach ($product->variations as $variation) {
                            $selectedValueUid = null;

                            foreach ($variation->values as $value) {
                                if (isset($valueUidSet[$value->uid])) {
                                    $selectedValueUid = (string) $value->uid;
                                    break;
                                }
                            }

                            if (! $selectedValueUid) {
                                continue;
                            }

                            $label = $valueLabelsByUid[$selectedValueUid] ?? null;
                            if (! is_string($label) || $label === '') {
                                continue;
                            }

                            $labelSlug = Str::slug($label);
                            if ($labelSlug !== '') {
                                $parts[] = $labelSlug;
                            }
                        }

                        $computedVariantSlug = implode('-', $parts);

                        if ($computedVariantSlug !== '' && $computedVariantSlug === $candidateVariantSlug) {
                            $product->setRelation('variant', $variant);
                            $variantResolvedFromCleanSlug = true;
                            break;
                        }
                    }
                }

                // Continue with the rest of the existing controller flow unchanged.
            } else {
                // 1. Check for inactive product specific redirection settings
                $inactiveProduct = Product::withoutGlobalScope('active')->where('slug', $slug)->first();
                
                if ($inactiveProduct && !$inactiveProduct->is_active) {
                    $rType = $inactiveProduct->redirect_type ?? '404'; // Default to 404 if null
                    $rTargetId = $inactiveProduct->redirect_target_id;
                    
                    if ($rType === '410') {
                        abort(410);
                    }
                    
                    if ($rTargetId) {
                        $statusCode = str_contains($rType, '301') ? 301 : 302;
                        
                        if (str_contains($rType, 'category')) {
                            $targetCat = Category::find($rTargetId);
                            if ($targetCat) {
                                return redirect($targetCat->url(), $statusCode);
                            }
                        } elseif (str_contains($rType, 'product')) {
                            $targetProd = Product::find($rTargetId);
                            if ($targetProd) {
                                return redirect($targetProd->url(), $statusCode);
                            }
                        }
                    }
                    // If type is 404 or target missing, fall through to UrlRedirect check
                }

                $baseSlug = setting('products_page_slug', 'products');
                $sourcePath = '/' . $baseSlug . '/' . ltrim($slug, '/');
                $redirect = UrlRedirect::where('source_path', $sourcePath)
                    ->where('is_active', true)
                    ->first();

                if ($redirect) {
                    $code = in_array((int) $redirect->status_code, [301, 302]) ? (int) $redirect->status_code : 301;
                    if ($redirect->target_type === 'product' && $redirect->target_id) {
                        $target = app(ProductRepository::class)->find($redirect->target_id);
                        return redirect($target->url(), $code);
                    }
                    if ($redirect->target_type === 'category' && $redirect->target_id) {
                        return redirect('/categories/' . $redirect->target_id, $code);
                    }
                    if ($redirect->target_type === 'home') {
                        return redirect('/', $code);
                    }
                    if ($redirect->target_type === 'custom' && $redirect->target_url) {
                        return redirect($redirect->target_url, $code);
                    }
                }

                $defaultStatus = (int) config('storefront.deleted_product_status', 410);
                abort(in_array($defaultStatus, [404, 410]) ? $defaultStatus : 410);
            }
        }

        $locale = locale();

        $relatedProducts = collect();
        $upSellProducts = collect();

        $reviewCacheKey = 'storefront:product:' . (int) $product->id . ':review:' . $locale;
        $review = Cache::store('file')->remember($reviewCacheKey, now()->addMinutes(10), function () use ($product) {
            return $this->getReviewData($product);
        });
        $product->append([
            'is_in_flash_sale',
            'unit_min',
            'unit_step',
            'unit_suffix',
            'unit_decimal',
        ]);

        // Ensure frontend relations are loaded for Alpine.js
        try {
            $product->loadMissing([
                'files',
                'variants.files',
                'variations.values',
                'variations.values.files',
                'options.values',
                'productMedia' => function ($q) {
                    $q->active()->orderBy('position');
                }
            ]);
        } catch (\Throwable $e) {}

        $flashSalePrice = false;

        if ($product->is_in_flash_sale) {
            $pivot = FlashSale::pivot($product);
            if ($pivot->end_date->isFuture()) {
                $flashSalePrice = FlashSale::pivot($product)->price->convertToCurrentCurrency();
            }
        }

        try {
            $productId = (int) $product->id;

            $relatedCacheKey = "storefront:product:{$productId}:related:ssr:{$locale}:v2";
            $upsellCacheKey = "storefront:product:{$productId}:upsell:ssr:{$locale}:v2";

            $relatedProducts = Cache::store('file')->remember($relatedCacheKey, now()->addMinutes(5), function () use ($product) {
                $items = $product->relatedProducts()
                    ->with(['variants', 'variations', 'tags'])
                    ->withAvg('reviews', 'rating')
                    ->forCard()
                    ->take(6)
                    ->get();

                return $this->normalizeProductsForCard($items);
            });

            $upSellProducts = Cache::store('file')->remember($upsellCacheKey, now()->addMinutes(5), function () use ($product) {
                $items = $product->upSellProducts()
                    ->with(['variants', 'variations', 'tags'])
                    ->withAvg('reviews', 'rating')
                    ->forCard()
                    ->take(6)
                    ->get();

                return $this->normalizeProductsForCard($items);
            });
        } catch (\Throwable $e) {
            $relatedProducts = collect();
            $upSellProducts = collect();
        }

        $requestedVariant = request()->query('variant');

        if ($requestedVariant) {
            $matchedVariant = $product->variants()
                ->withoutGlobalScope('active')
                ->where('uid', $requestedVariant)
                ->firstOrFail();

            $product->setRelation('variant', $matchedVariant);
            $valueUids = array_filter(explode('.', (string) $matchedVariant->uids));
            $product->loadMissing(['variations.values', 'variations.values.files']);
            $readableParams = [];
            foreach ($product->variations as $variation) {
                $key = Str::slug($variation->name);
                if (!$key) {
                    continue;
                }
                $selectedValue = $variation->values->first(function ($value) use ($valueUids) {
                    return in_array($value->uid, $valueUids, true);
                });
                if (!$selectedValue) {
                    continue;
                }
                $valueSlug = Str::slug($selectedValue->label);
                if (!$valueSlug) {
                    continue;
                }
                $readableParams[$key] = $valueSlug;
            }
            $targetUrl = route('products.show', $product->slug);
            if (!empty($readableParams)) {
                $targetUrl .= '?' . http_build_query($readableParams);
            }
            return redirect()->to($targetUrl, 301);
        } else {
            // If the variant was resolved from the clean URL slug, keep it as-is.
            // Otherwise fall back to the existing readable-params/default variant logic.
            if (! $variantResolvedFromCleanSlug) {
                $product->loadMissing(['variations.values', 'variations.values.files']);

                $hasVariationQueryKeyUsed = false;
                $matchedAnyVariationValue = false;

                $selectedUids = [];
                foreach ($product->variations as $variation) {
                    $key = Str::slug($variation->name);
                    $raw = request()->query($key);
                    if (!$raw) {
                        continue;
                    }

                    // If a variation query key exists but is invalid, we may redirect to base URL later.
                    $hasVariationQueryKeyUsed = true;

                    $valueSlug = Str::slug($raw);
                    $valueUid = null;
                    foreach ($variation->values as $value) {
                        if (Str::slug($value->label) === $valueSlug) {
                            $valueUid = $value->uid;
                            $matchedAnyVariationValue = true;
                            break;
                        }
                    }

                    if ($valueUid) {
                        $selectedUids[] = $valueUid;
                    }
                }

                // UX: if readable variation params are present but none match, redirect to base product URL.
                // Preserve non-variation params (utm_*, gclid, fbclid...) and remove only variation keys.
                if ($hasVariationQueryKeyUsed && ! $matchedAnyVariationValue) {
                    $baseUrl = route('products.show', $product->slug);
                    $preserved = request()->query();

                    $preserved['variant'] = null;
                    unset($preserved['variant']);

                    foreach ($product->variations as $variation) {
                        $k = Str::slug($variation->name);
                        if ($k) {
                            unset($preserved[$k]);
                        }
                    }

                    if (! empty($preserved)) {
                        $baseUrl .= '?' . http_build_query($preserved);
                    }

                    return redirect()->to($baseUrl, 302);
                }

                if (!empty($selectedUids)) {
                    sort($selectedUids);
                    $uidsString = implode('.', $selectedUids);

                    $matched = $product->variants()
                        ->withoutGlobalScope('active')
                        ->where('uids', $uidsString)
                        ->first();

                    if ($matched) {
                        $product->setRelation('variant', $matched);
                    } else {
                        $product->setRelation('variant', $product->variants()
                            ->withoutGlobalScope('active')
                            ->default()
                            ->first());
                    }
                } else {
                    $product->setRelation('variant', $product->variants()
                        ->withoutGlobalScope('active')
                        ->default()
                        ->first());
                }
            }
        }

        // Build unified gallery: cache to reduce TTFB (media + video merge)
        $variantIdForCache = (int) (optional($product->variant)->id ?? 0);
        $galleryCacheKey = 'storefront:product:' . (int) $product->id . ':gallery:' . $variantIdForCache . ':' . $locale . ':v1';
        $galleryPayload = Cache::store('file')->remember($galleryCacheKey, now()->addMinutes(10), function () use ($product) {
            $gallery = [];
            $baseId = $product->base_image->id ?? null;

            if ($product->base_image) {
                $gallery[] = [
                    'type' => 'image',
                    'src' => $product->base_image->detail_jpeg_url ?? $product->base_image->path,
                    'thumb' => $product->base_image->thumb_jpeg_url ?? $product->base_image->path,
                    'alt' => $product->name,
                ];
            }

            foreach ($product->media as $media) {
                $rawPath = $media->getRawOriginal('path');
                $ext = strtolower(pathinfo((string) $rawPath, PATHINFO_EXTENSION));
                $isVideo = in_array($ext, ['mp4', 'webm', 'ogg']);
                if (!$isVideo) {
                    // skip base image duplicate
                    if ($baseId && $media->id === $baseId) continue;
                    $gallery[] = [
                        'type' => 'image',
                        'src' => $media->detail_jpeg_url ?? $media->path,
                        'thumb' => $media->thumb_jpeg_url ?? $media->path,
                        'alt' => $product->name,
                    ];
                }
            }

            $variantVideos = collect();
            try {
                $productMedia = $product->relationLoaded('productMedia') ? $product->productMedia : collect();

                $videos = $productMedia
                    ->where('is_active', true)
                    ->where('type', 'video')
                    ->sortBy('position')
                    ->values();

                if ($product->variant) {
                    $variantVideos = $videos->where('variant_id', $product->variant->id)->values();

                    if ($variantVideos->isEmpty()) {
                        $variantVideos = $videos->where('variant_id', null)->values();
                    }
                } else {
                    $variantVideos = $videos->where('variant_id', null)->values();
                }
            } catch (\Throwable $e) {}

            foreach ($variantVideos as $media) {
                $variantBase = optional($product->variant)->base_image?->path;
                $poster = $media->poster ?: ($variantBase ?: ($product->base_image?->path ?? asset('build/assets/image-placeholder.png')));
                $gallery[] = [
                    'type' => 'video',
                    'src' => $media->path,
                    'thumb' => $poster,
                    'alt' => 'Ürün video – ' . $product->name,
                ];
            }

            if (!empty($gallery)) {
                if ($gallery[0]['type'] !== 'image') {
                    // ensure first item is always image for LCP
                    usort($gallery, function ($a, $b) {
                        return ($a['type'] === 'image' ? 0 : 1) <=> ($b['type'] === 'image' ? 0 : 1);
                    });
                }
            }

            $firstVideo = null;
            foreach ($gallery as $gi) {
                if (($gi['type'] ?? '') === 'video') { $firstVideo = $gi; break; }
            }
            $hasVideo = $firstVideo !== null;
            $videoUrl = $hasVideo ? ($firstVideo['src'] ?? null) : null;
            $videoThumbnailUrl = $hasVideo ? ($firstVideo['thumb'] ?? ($product->base_image?->path ?? null)) : null;

            return [
                'gallery' => $gallery,
                'hasVideo' => $hasVideo,
                'videoUrl' => $videoUrl,
                'videoThumbnailUrl' => $videoThumbnailUrl,
            ];
        });

        $gallery = $galleryPayload['gallery'] ?? [];
        $hasVideo = (bool) ($galleryPayload['hasVideo'] ?? false);
        $videoUrl = $galleryPayload['videoUrl'] ?? null;
        $videoThumbnailUrl = $galleryPayload['videoThumbnailUrl'] ?? null;

        event(new ProductViewed($product));

        $upsellOffer = $upsellService->resolveBestRule(\Modules\Cart\Facades\Cart::instance(), 'product', $product);

        return view('storefront::public.products.show', compact(
            'product',
            'review',
            'relatedProducts',
            'upSellProducts',
            'flashSalePrice',
            'gallery',
            'hasVideo',
            'videoUrl',
            'videoThumbnailUrl',
            'upsellOffer'
        ));
    }


    public function related($id)
    {
        $productId = (int) $id;
        $locale = locale();

        $cacheKey = "storefront:product:{$productId}:related:{$locale}:v3";

        return Cache::store('file')->remember($cacheKey, now()->addMinutes(5), function () use ($productId) {
            $product = Product::query()->select(['id'])->findOrFail($productId);

            $items = $product->relatedProducts()
                ->with(['tags'])
                ->withAvg('reviews', 'rating')
                ->forCard()
                ->take(6)
                ->get();

            return $this->normalizeProductsForCard($items);
        });
    }


    public function upsell($id)
    {
        $productId = (int) $id;
        $locale = locale();

        $cacheKey = "storefront:product:{$productId}:upsell:{$locale}:v2";

        return Cache::store('file')->remember($cacheKey, now()->addMinutes(5), function () use ($productId) {
            $product = Product::query()->select(['id'])->findOrFail($productId);

            $items = $product->upSellProducts()
                ->with(['variants', 'brand.translations', 'saleUnit', 'tags'])
                ->withAvg('reviews', 'rating')
                ->forCard()
                ->take(8)
                ->get();

            return $this->normalizeProductsForCard($items);
        });
    }


    private function normalizeProductsForCard($products)
    {
        $collection = $products instanceof \Illuminate\Support\Collection
            ? $products
            : collect($products);

        $allTagIds = $collection
            ->flatMap(function (Product $p) {
                return $p->relationLoaded('tags') ? $p->tags->pluck('id') : collect();
            })
            ->filter()
            ->unique()
            ->values();

        $badgesByTagId = collect();

        if ($allTagIds->isNotEmpty()) {
            $badgesByTagId = TagBadge::query()
                ->active()
                ->where('show_on_listing', true)
                ->whereIn('tag_id', $allTagIds)
                ->orderByDesc('priority')
                ->get()
                ->groupBy('tag_id');
        }

        return $collection->map(function (Product $product) use ($badgesByTagId) {
            $tagIds = $product->relationLoaded('tags') ? $product->tags->pluck('id')->all() : [];

            $tagBadges = collect($tagIds)
                ->flatMap(function ($tagId) use ($badgesByTagId) {
                    return $badgesByTagId->get($tagId, collect());
                })
                ->unique('id')
                ->map(function ($badge) {
                    return [
                        'name' => $badge->name,
                        'image_url' => $badge->image_url,
                        'listing_position' => $badge->listing_position,
                        'detail_position' => $badge->detail_position,
                        'priority' => $badge->priority,
                    ];
                })
                ->values();

            $variantLabel = optional($product->variations->first())->name;

            $base = $product->clean();
            $base['variant_attribute_label'] = $variantLabel;
            $base['base_image_thumb'] = [
                'path' => media_variant_url(
                    $product->base_image,
                    (int) config('image_optimization.variants.widths.grid', 400)
                ),
            ];
            $base['tag_badges'] = $tagBadges;

            $avg = (float) ($product->reviews_avg_rating ?? 0);
            $base['rating_percent'] = $avg > 0 ? ($avg / 5) * 100 : 0;

            return $base;
        })->values();
    }


    private function getReviewData(Product $product)
    {
        if (!setting('reviews_enabled')) {
            return null;
        }

        return Review::countAndAvgRating($product);
    }
}

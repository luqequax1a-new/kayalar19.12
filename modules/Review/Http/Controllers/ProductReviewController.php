<?php

namespace Modules\Review\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\Review\Entities\Review;
use Modules\Product\Entities\Product;
use Modules\Order\Entities\Order;
use Modules\Review\Http\Requests\StoreReviewRequest;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
 

class ProductReviewController
{
    /**
     * Display a listing of the resource.
     *
     * @param int $productId
     *
     * @return Response
     */
    public function index($productId)
    {
        $productId = (int) $productId;
        $locale = locale();

        $page = (int) request('page', 1);
        $sort = (string) request('sort', 'latest');
        $rating = request('rating');
        $rating = $rating !== null ? (int) $rating : null;

        $cacheKey = 'storefront:product:' . $productId . ':reviews:' . $locale . ':' . md5(json_encode([
            'page' => $page,
            'sort' => $sort,
            'rating' => $rating,
        ])) . ':v1';

        return Cache::store('file')->remember($cacheKey, now()->addMinutes(10), function () use ($productId, $sort, $rating) {
            $query = Review::query()
                ->where('product_id', $productId)
                ->with(['files']);

            if ($sort === 'highest') {
                $query->orderByDesc('rating');
            } elseif ($sort === 'lowest') {
                $query->orderBy('rating');
            } else {
                $query->latest();
            }

            if ($rating !== null && $rating > 0) {
                $query->where('rating', $rating);
            }

            return $query->paginate(5);
        });
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param int $productId
     * @param StoreReviewRequest $request
     *
     * @return Response
     */
    public function store($productId, StoreReviewRequest $request)
    {
        if (!setting('reviews_enabled')) {
            return;
        }
        $orderId = (int) ($request->input('order_id') ?? 0);
        $validOrderId = null;

        if ($orderId > 0) {
            $query = Order::query()
                ->where('id', $orderId)
                ->where('status', Order::COMPLETED);

            // Kullanıcı giriş yaptıysa, siparişin gerçekten bu müşteriye ait olduğunu doğrula
            if (auth()->check()) {
                $query->where('customer_id', auth()->id());
            }

            if ($validOrder = $query->first()) {
                $validOrderId = $validOrder->id;
            }
        }
        $review = Product::findOrFail($productId)
            ->reviews()
            ->create([
                'reviewer_id' => auth()->id(),
                'rating' => $request->rating,
                'reviewer_name' => $request->reviewer_name,
                'comment' => $request->comment,
                'is_approved' => setting('auto_approve_reviews', 0),
                'order_id' => $validOrderId,
            ]);

        event(new \Modules\Review\Events\ReviewCreated($review));

        // Handle image uploads (max 4) and attach to review via media module
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $files = is_array($files) ? $files : [$files];
            $files = array_slice($files, 0, 4);

            $storedIds = [];
            foreach ($files as $uploaded) {
                try {
                    if (!$uploaded->isValid()) { continue; }
                    // Always use public disk so Storage::url(...) resolves to /storage/...
                    $path = $uploaded->store('media', 'public');
                    $file = File::create([
                        'user_id' => auth()->id() ?: 0,
                        'filename' => $uploaded->getClientOriginalName(),
                        'disk' => 'public',
                        'path' => $path,
                        'extension' => $uploaded->getClientOriginalExtension(),
                        'mime' => $uploaded->getClientMimeType(),
                        'size' => $uploaded->getSize(),
                    ]);
                    // Responsive image varyantlarını kuyrukta üret, isteği bloklama
                    try {
                        dispatch(new \Modules\Media\Jobs\GenerateResponsiveImagesForMedia($file->id));
                    } catch (\Throwable $e) {}
                    $storedIds[] = $file->id;
                } catch (\Throwable $e) {
                    \Log::warning('Review image store failed', [
                        'product_id' => $productId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (!empty($storedIds)) {
                $review->syncFiles(['review_images' => $storedIds]);
            }
        }

        return $review->load(['files']);
    }
}

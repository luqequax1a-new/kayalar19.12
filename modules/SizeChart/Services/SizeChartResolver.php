<?php

namespace Modules\SizeChart\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Modules\Tag\Entities\Tag;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\SizeChart\Entities\SizeChart;

class SizeChartResolver
{
    public function resolveForProduct(Product $product): ?SizeChart
    {
        return $this->resolveAllForProduct($product)->first();
    }

    /**
     * @return Collection<int, SizeChart>
     */
    public function resolveAllForProduct(Product $product): Collection
    {
        return Cache::tags(['size_charts', 'products'])->remember(
            $this->cacheKey($product->id) . '.all',
            now()->addMinutes(10),
            function () use ($product) {
                return $this->resolveAllForProductUncached($product);
            }
        );
    }

    private function cacheKey(int $productId): string
    {
        return "size_charts.product.{$productId}";
    }

    /**
     * @return Collection<int, SizeChart>
     */
    private function resolveAllForProductUncached(Product $product): Collection
    {
        $product->loadMissing(['tags']);

        $productOverrides = SizeChart::query()
            ->withoutGlobalScope('active')
            ->where('is_active', true)
            ->whereHas('assignments', function ($q) use ($product) {
                $q->where('assignable_type', Product::class)
                    ->where('assignable_id', $product->id);
            })
            ->with(['assignments' => function ($q) use ($product) {
                $q->where('assignable_type', Product::class)
                    ->where('assignable_id', $product->id)
                    ->orderByDesc('priority')
                    ->orderByDesc('id');
            }])
            ->get()
            ->sortByDesc(function (SizeChart $chart) {
                return optional($chart->assignments->first())->priority ?? 0;
            })
            ->values();

        if ($productOverrides->isNotEmpty()) {
            return $productOverrides;
        }

        if (! is_null($product->primary_category_id)) {
            $categoryMatches = SizeChart::query()
                ->withoutGlobalScope('active')
                ->where('is_active', true)
                ->whereHas('assignments', function ($q) use ($product) {
                    $q->where('assignable_type', Category::class)
                        ->where('assignable_id', $product->primary_category_id);
                })
                ->with(['assignments' => function ($q) use ($product) {
                    $q->where('assignable_type', Category::class)
                        ->where('assignable_id', $product->primary_category_id)
                        ->orderByDesc('priority')
                        ->orderByDesc('id');
                }])
                ->get()
                ->sortByDesc(function (SizeChart $chart) {
                    return optional($chart->assignments->first())->priority ?? 0;
                })
                ->values();

            if ($categoryMatches->isNotEmpty()) {
                return $categoryMatches;
            }
        }

        $tagIds = $product->tags->pluck('id')->all();

        if (empty($tagIds)) {
            return collect();
        }

        return SizeChart::query()
            ->withoutGlobalScope('active')
            ->where('is_active', true)
            ->whereHas('assignments', function ($q) use ($tagIds) {
                $q->where('assignable_type', Tag::class)
                    ->whereIn('assignable_id', $tagIds);
            })
            ->with(['assignments' => function ($q) use ($tagIds) {
                $q->where('assignable_type', Tag::class)
                    ->whereIn('assignable_id', $tagIds)
                    ->orderByDesc('priority')
                    ->orderByDesc('id');
            }])
            ->get()
            ->sortByDesc(function (SizeChart $chart) {
                return optional($chart->assignments->first())->priority ?? 0;
            })
            ->values();
    }
}

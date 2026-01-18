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
        return Cache::tags(['size_charts', 'products', 'categories'])->remember(
            $this->cacheKey($product->id),
            now()->addHours(1),
            function () use ($product) {
                return $this->resolveForProductUncached($product);
            }
        );
    }

    private function cacheKey(int $productId): string
    {
        return "size_charts.product.v3.{$productId}";
    }

    /**
     * @return Collection<int, SizeChart>
     */
    private function resolveForProductUncached(Product $product): Collection
    {
        // 1. Check for Product Specific Chart (Highest Priority)
        $productChart = $this->findForEntity(Product::class, $product->id);
        if ($productChart->isNotEmpty()) {
            return $productChart;
        }

        // 2. Check for Category Specific Chart (Recursive Parent Check)
        if ($product->primary_category_id) {
            $categoryChart = $this->findForCategoryRecursively($product->primary_category_id);
            if ($categoryChart->isNotEmpty()) {
                return $categoryChart;
            }
        }

        // 3. Check for Tag Specific Charts
        $product->loadMissing(['tags']);
        $tagIds = $product->tags->pluck('id')->all();

        if (! empty($tagIds)) {
            $tagCharts = $this->findForEntity(Tag::class, $tagIds);
            if ($tagCharts->isNotEmpty()) {
                return $tagCharts;
            }
        }

        return collect();
    }

    private function findForCategoryRecursively(int $categoryId): Collection
    {
        $currentId = $categoryId;

        // Traverse up the category tree
        // Limit iterations to prevent infinite loops in case of circular references (max 10 levels)
        for ($i = 0; $i < 10; $i++) {
            if (! $currentId) {
                break;
            }

            $charts = $this->findForEntity(Category::class, $currentId);

            if ($charts->isNotEmpty()) {
                return $charts;
            }

            // Find parent ID
            // Ideally this should be cached or eager loaded, but for now we query simple
            $parent = Category::select('parent_id')->where('id', $currentId)->first();
            
            if (! $parent || ! $parent->parent_id) {
                break;
            }

            $currentId = $parent->parent_id;
        }

        return collect();
    }

    /**
     * Generic finder for size charts assigned to an entity type and id(s).
     * @param string $type
     * @param int|array $ids
     * @return Collection
     */
    private function findForEntity(string $type, $ids): Collection
    {
        $ids = (array) $ids;

        if (empty($ids)) {
            return collect();
        }

        return SizeChart::query()
            ->withoutGlobalScope('active')
            ->where('is_active', true)
            ->whereHas('assignments', function ($q) use ($type, $ids) {
                $q->where('assignable_type', $type)
                  ->whereIn('assignable_id', $ids);
            })
            ->with(['assignments' => function ($q) use ($type, $ids) {
                $q->where('assignable_type', $type)
                  ->whereIn('assignable_id', $ids)
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

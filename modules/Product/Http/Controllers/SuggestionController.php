<?php

namespace Modules\Product\Http\Controllers;

use Closure;
use Modules\Product\Entities\Product;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Modules\Product\Http\Response\SuggestionsResponse;

class SuggestionController
{
    public function index(Product $model): SuggestionsResponse
    {
        $query = request('query');

        if (is_null($query) || $query === '') {
            return $this->popularSuggestions($model);
        }

        $products = $this->getProducts($model);

        return new SuggestionsResponse(
            $query,
            $products,
            $products->pluck('categories')->flatten(),
            $this->getTotalResults($model)
        );
    }


    /**
     * Get popular suggestions when query is empty.
     *
     * @param Product $model
     * @return SuggestionsResponse
     */
    private function popularSuggestions(Product $model): SuggestionsResponse
    {
        return new SuggestionsResponse(
            '',
            collect(),
            collect(),
            0,
            $this->getPopularSearches(),
            collect(),
            collect(),
            $this->getBestSellers($model),
            $this->getBestSellingCategories(),
            $this->getBestSellingBrands()
        );
    }


    /**
     * Get popular searches.
     *
     * @return Collection
     */
    private function getPopularSearches()
    {
        return \Illuminate\Support\Facades\DB::table('search_terms')
            ->orderByDesc('hits')
            ->limit(10)
            ->pluck('term');
    }


    /**
     * Get best selling products.
     *
     * @param Product $model
     * @return Collection
     */
    private function getBestSellers(Product $model)
    {
        return $model->newQuery()
            ->forCard()
            ->with([
                'variants' => function ($q) {
                    $q->where('is_active', true)
                        ->orderBy('position');
                },
                'variations' => function ($q) {
                    $q->without(['values'])
                        ->select(['variations.id', 'variations.uid', 'variations.type', 'variations.is_global', 'variations.position'])
                        ->with(['translations:id,variation_id,locale,name']);
                },
            ])
            ->where('is_active', true)
            ->orderByDesc('in_stock')
            ->orderByDesc('viewed')
            ->limit(10)
            ->get();
    }


    /**
     * Get best selling categories.
     *
     * @return Collection
     */
    private function getBestSellingCategories()
    {
        return \Modules\Category\Entities\Category::where('is_active', true)
            ->where('is_searchable', true)
            ->withCount('products')
            ->orderByDesc('products_count')
            ->having('products_count', '>', 0)
            ->with(['files'])
            ->limit(5)
            ->get();
    }


    /**
     * Get best selling brands.
     *
     * @return Collection
     */
    private function getBestSellingBrands()
    {
        return \Modules\Brand\Entities\Brand::where('is_active', true)
            ->withCount('products')
            ->orderByDesc('products_count')
            ->having('products_count', '>', 0)
            ->limit(5)
            ->get();
    }


    /**
     * Get products suggestions.
     *
     * @param Product $model
     *
     * @return Collection
     */
    private function getProducts(Product $model)
    {
        return $model->search(request('query'))
            ->query()
            ->limit(10)
            ->withName()
            ->withBaseImage()
            ->withPrice()
            ->with([
                'variants' => function ($q) {
                    $q->where('is_active', true)
                        ->orderBy('position');
                },
                'variations' => function ($q) {
                    $q->without(['values'])
                        ->select(['variations.id', 'variations.uid', 'variations.type', 'variations.is_global', 'variations.position'])
                        ->with(['translations:id,variation_id,locale,name']);
                },
                'files',
                'categories' => function ($query) {
                    $query->limit(5);
                }
            ])
            ->addSelect([
                'products.id',
                'products.slug',
                'products.in_stock',
                'products.manage_stock',
                'products.qty',
                'products.list_variants_separately',
            ])
            ->when(request()->filled('category'), $this->categoryQuery())
            ->get();
    }


    /**
     * Returns categories condition closure.
     *
     * @return Closure
     */
    private function categoryQuery()
    {
        return function (Builder $query) {
            $query->whereHas('categories', function ($categoryQuery) {
                $categoryQuery->where('slug', request('category'));
            });
        };
    }


    /**
     * Get totalPrice results count.
     *
     * @param Product $model
     *
     * @return int
     */
    private function getTotalResults(Product $model): int
    {
        return $model->search(request('query'))
            ->query()
            ->when(request()->filled('category'), $this->categoryQuery())
            ->count();
    }
}

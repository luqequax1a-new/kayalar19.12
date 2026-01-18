<?php

namespace Modules\Product\Filters;

use Modules\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeValue;
use Modules\Category\Entities\Category;

class QueryStringFilter
{
    private $sorts = [
        'relevance',
        'alphabetic',
        'toprated',
        'latest',
        'pricelowtohigh',
        'pricehightolow',
    ];

    private $groupColumns = [
        'products.id',
        'slug',
        'price',
        'selling_price',
        'special_price',
        'special_price_type',
        'special_price_start',
        'special_price_end',
        'in_stock',
        'manage_stock',
        'qty',
        'new_from',
        'new_to',
        'category_position',
    ];


    public function sort($query, $sortType)
    {
        // Whitelist validation for security
        if (!$this->sortTypeExists($sortType)) {
            return;
        }

        // Clear any existing category position ordering when user explicitly selects a sort
        // This ensures user's choice takes precedence
        $orders = $query->getQuery()->orders ?? [];
        $query->getQuery()->orders = array_filter($orders, function($order) {
            // Remove category_position orders to let user sort take over
            if (is_array($order) && isset($order['column'])) {
                return strpos($order['column'], 'category_position') === false;
            }
            if (is_string($order)) {
                return strpos($order, 'category_position') === false;
            }
            return true;
        });

        return $this->{$sortType}($query);
    }


    public function relevance($query)
    {
        // For relevance, if category position exists, use it as default
        // Otherwise products are searched by relevant order by default
        $selectColumns = $query->getQuery()->columns ?? [];
        $hasCategoryPosition = false;
        
        foreach ($selectColumns as $column) {
            if (is_string($column) && strpos($column, 'category_position') !== false) {
                $hasCategoryPosition = true;
                break;
            }
        }

        if ($hasCategoryPosition) {
            // Category-specific position ordering
            $query->orderByRaw('category_position IS NULL, category_position ASC');
        } else {
            // Main products page: use main_page_position column
            // This is a dedicated column for /products page sorting
            \Log::info('RELEVANCE: Using main_page_position ordering');
            $query->orderByRaw('main_page_position IS NULL, main_page_position ASC');
        }
    }


    public function alphabetic($query)
    {
        $query->join('product_translations', 'products.id', '=', 'product_translations.product_id')
            ->where('product_translations.locale', locale())
            ->addSelect('product_translations.name')
            ->orderBy('product_translations.name', 'asc');
    }


    public function topRated($query)
    {
        $query->orderByDesc('reviews_avg_rating');
    }


    public function latest($query)
    {
        $query->latest();
    }


    public function priceLowToHigh($query)
    {
        $query->orderBy('selling_price');
    }


    public function priceHighToLow($query)
    {
        $query->orderByDesc('selling_price');
    }


    public function fromPrice($query, $price)
    {
        $convertedPrice = $this->convertPrice($price);

        $query->where(function ($productQuery) use ($convertedPrice) {
            $productQuery->where('selling_price', '>=', $convertedPrice);
            $productQuery->orWhereHas('variants', function ($variantQuery) use ($convertedPrice) {
                $variantQuery->where('selling_price', '>=', $convertedPrice);
            });
        });
    }


    public function toPrice($query, $price)
    {
        $convertedPrice = $this->convertPrice($price);

        $query->where(function ($productQuery) use ($convertedPrice) {
            $productQuery->where('selling_price', '<=', $convertedPrice);
            $productQuery->orWhereHas('variants', function ($variantQuery) use ($convertedPrice) {
                $variantQuery->where('selling_price', '<=', $convertedPrice);
            });
        });
    }


    public function brand($query, $slug)
    {
        $slugs = array_filter(is_array($slug) ? $slug : explode(',', (string) $slug));
        
        if (empty($slugs)) {
            return;
        }

        $query->whereHas('brand', function ($brandQuery) use ($slugs) {
            $brandQuery->whereIn('slug', $slugs);
        });
    }


    public function category($query, $slug)
    {
        if ($slug === setting('products_page_slug', 'products')) {
            return;
        }

        $category = Category::where('slug', $slug)->first();

        if ($category) {
            $categoryIds = $category->descendantsAndSelfIds();

            $query->whereHas('categories', function ($categoryQuery) use ($categoryIds) {
                $categoryQuery->whereIn('categories.id', $categoryIds);
            });

            $query->leftJoin('product_categories as pc_sort', function($join) use ($category) {
                $join->on('products.id', '=', 'pc_sort.product_id')
                     ->where('pc_sort.category_id', '=', $category->id);
            });

            $query->addSelect('pc_sort.position as category_position');
            $query->orderByRaw('category_position IS NULL, category_position ASC');
        } else {
            $query->whereHas('categories', function ($categoryQuery) use ($slug) {
                $categoryQuery->where('slug', $slug);
            });
        }
    }


    public function tag($query, $slug)
    {
        $query->whereHas('tags', function ($tagQuery) use ($slug) {
            $tagQuery->where('slug', $slug);
        });
    }


    public function rating($query, $minRating)
    {
        $minRating = (float) $minRating;
        
        if ($minRating > 0) {
            $query->where(function ($query) use ($minRating) {
                $query->whereRaw("(SELECT AVG(rating) FROM reviews WHERE products.id = reviews.product_id AND is_approved = 1) >= ?", [$minRating]);
            });
        }
    }


    public function attribute($query, $attributeFilters)
    {
        foreach ($attributeFilters as $slug => $values) {
            if (empty($values)) {
                continue;
            }

            // For range filters, if both min/max are empty/null, skip.
            if (is_array($values) && (isset($values['min']) || isset($values['max']))) {
                $hasMin = isset($values['min']) && $values['min'] !== '';
                $hasMax = isset($values['max']) && $values['max'] !== '';
                if (!$hasMin && !$hasMax) {
                    continue;
                }
            }

            $query->whereHas('attributes', function ($q) use ($slug, $values) {
                $q->whereHas('attribute', function($aq) use ($slug) {
                    $aq->where('slug', $slug);
                });

                $q->whereHas('values.attributeValue', function ($vq) use ($values) {
                    $vq->whereHas('translations', function ($tq) use ($values) {
                        if (is_array($values) && (isset($values['min']) || isset($values['max']))) {
                            if (isset($values['min']) && $values['min'] !== '') {
                                $tq->where(DB::raw('CAST(value AS DECIMAL(10,2))'), '>=', $values['min']);
                            }
                            if (isset($values['max']) && $values['max'] !== '') {
                                $tq->where(DB::raw('CAST(value AS DECIMAL(10,2))'), '<=', $values['max']);
                            }
                        } else {
                            $vArray = array_filter((array) $values);
                            if (!empty($vArray)) {
                                $tq->whereIn('value', $vArray);
                            }
                        }
                    });
                });
            });
        }
    }


    private function sortTypeExists($sortType)
    {
        return in_array(strtolower($sortType), $this->sorts);
    }


    private function convertPrice($price)
    {
        return Money::inCurrentCurrency($price)->convertToDefaultCurrency()->amount();
    }


    private function getAttributeIds($attributeFilters)
    {
        return Attribute::whereIn('slug', array_keys($attributeFilters))->pluck('id');
    }


    private function getAttributeValueIds($attributeFilters)
    {
        return once(function () use ($attributeFilters) {
            return AttributeValue::whereTranslationIn('value', array_flatten($attributeFilters))
                ->pluck('id')
                ->implode(',') ?: 'null';
        });
    }
}

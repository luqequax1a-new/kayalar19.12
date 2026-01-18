<?php

namespace Modules\Product\Filters;

use Illuminate\Http\Request;

class ProductFilter
{
    private $request;
    private $queryStringFilter;


    public function __construct(Request $request, QueryStringFilter $queryStringFilter)
    {
        $this->request = $request;
        $this->queryStringFilter = $queryStringFilter;
    }


    public function apply($query)
    {
        $query = $query->forCard();

        // Apply all filters EXCEPT sort (we'll handle sort separately)
        foreach ($this->filters() as $name => $value) {
            if ($name === 'sort') {
                continue; // Skip sort, we'll handle it below
            }
            
            if (!is_null($value)) {
                $this->queryStringFilter->{$name}($query, $value);
            }
        }

        // Normalize sort parameter: treat empty string as null
        $sortParam = $this->request->get('sort');
        if (is_string($sortParam)) {
            $sortParam = trim($sortParam);
            if ($sortParam === '') {
                $sortParam = null;
            }
        }

        $hasCategory = $this->request->has('category') && $this->request->get('category');
        $hasQuery = $this->request->has('query') && $this->request->get('query');
        
        // SPECIAL CASE: Convert "latest" to "relevance" for main/category pages
        // This ensures position-based sorting is used instead of date-based
        if ($sortParam === 'latest' && !$hasQuery) {
            \Log::info('ProductFilter: Converting latest to relevance for position-based sorting');
            $sortParam = 'relevance';
        }

        // Apply default sort if no explicit sort is provided
        // This ensures position ordering is used by default
        $hasSort = !empty($sortParam);
        
        \Log::info('ProductFilter::apply DEBUG', [
            'raw_sort' => $this->request->get('sort'),
            'normalized_sort' => $sortParam,
            'hasSort' => $hasSort,
            'hasCategory' => $hasCategory,
            'category_value' => $this->request->get('category'),
            'hasQuery' => $hasQuery,
            'query_value' => $this->request->get('query'),
            'will_apply_relevance' => !$hasSort && ($hasCategory || !$hasQuery),
        ]);
        
        if (!$hasSort) {
            // Apply relevance sort for:
            // 1. Category pages (category parameter exists)
            // 2. Main products page (no category, no query - just browsing all products)
            if ($hasCategory || !$hasQuery) {
                \Log::info('ProductFilter: Calling relevance sort (default)');
                $this->queryStringFilter->sort($query, 'relevance');
            } else {
                \Log::info('ProductFilter: NOT applying relevance (has query but no category)');
            }
        } else {
            \Log::info('ProductFilter: Applying sort (normalized)', ['sort' => $sortParam]);
            $this->queryStringFilter->sort($query, $sortParam);
        }

        return $query;
    }


    private function filters()
    {
        return array_filter($this->request->query(), function ($filter) {
            return $this->filterExists($filter);
        }, ARRAY_FILTER_USE_KEY);
    }


    private function filterExists($filter)
    {
        return method_exists($this->queryStringFilter, $filter) &&
            is_callable([$this->queryStringFilter, $filter]);
    }
}

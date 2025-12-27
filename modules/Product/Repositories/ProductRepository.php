<?php

namespace Modules\Product\Repositories;

use Modules\Product\Entities\Product;

class ProductRepository
{
    public static function list($ids = [])
    {
        return Product::select('id')
            ->withName()
            ->whereIn('id', $ids)
            ->when(!empty($ids), function ($query) use ($ids) {
                $idsString = collect($ids)
                    ->filter()
                    ->implode(',');

                $query->orderByRaw("FIELD(id, {$idsString})");
            })
            ->get()
            ->mapWithKeys(function ($product) {
                return [$product->id => $product->name];
            });
    }


    public static function findBySlug($slug)
    {
        return Product::withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->with([
                'meta',
                'brand',
                'primaryCategory',
                'saleUnit',
                'productMedia' => function ($q) {
                    $q->active()->orderBy('position');
                },
                'variations',
                'variations.values',
                'variations.values.files',
                'variants' => function ($q) {
                    $q->withoutGlobalScope('active')
                        ->withTrashed()
                        ->withBaseImage()
                        ->orderBy('position');
                },
                'categories',
                'tags',
                'attributes.attribute.attributeSet',
                'options',
                'files',
            ])
            ->where('slug', $slug)
            ->firstOrFail();
    }
}

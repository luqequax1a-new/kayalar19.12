<?php

namespace Modules\Product\Entities\Concerns;

trait QueryScopes
{
    public function scopeForCard($query): void
    {
        $query
            ->withName()
            ->withMedia()
            ->with('saleUnit')
            ->withPrice()
            ->withCount('options')
            // Only load review count and average, not all reviews (performance optimization)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->with(['variant' => function($q){
                $q->addSelect([
                    'id','product_id','uid','is_default',
                    'price','special_price','special_price_type','special_price_start','special_price_end',
                    'selling_price'
                ]);
            }])
            ->withStock()
            ->withNew()
            ->addSelect(['products.sale_unit_id'])
            ->addSelect(['products.list_variants_separately'])
            ->addSelect(
                [
                    'products.id',
                    'products.slug',
                    'products.created_at',
                ]
            );
    }


    public function scopeWithName($query): void
    {
        $query->with('translations:id,product_id,locale,name');
    }


    public function scopeWithStock($query): void
    {
        $query->addSelect(
            [
                'products.in_stock',
                'products.manage_stock',
                'products.qty',
            ]
        );
    }


    public function scopeWithNew($query): void
    {
        $query->addSelect(
            [
                'products.new_from',
                'products.new_to',
            ]
        );
    }


    public function scopeWithPrice($query): void
    {
        $query->addSelect(
            [
                'products.price',
                'products.special_price',
                'products.special_price_type',
                'products.selling_price',
                'products.special_price_start',
                'products.special_price_end',
            ]
        );
    }


    public function scopeWithBaseImage($query): void
    {
        $query->with([
            'files' => function ($q) {
                $q->wherePivot('zone', 'base_image');
            },
        ]);
    }


    public function scopeWithAdditionalImages($query): void
    {
        $query->with([
            'files' => function ($q) {
                $q->wherePivot('zone', 'addtional_iamges');
            },
        ]);
    }

    
    public function scopeWithMedia($query): void
    {
        $query->with([
            'files' => function ($q) {
                $q->wherePivotIn('zone', ['base_image', 'additional_images']);
            },
        ]);
    }
}

<?php

namespace Modules\Product\Transformers;

use Illuminate\Http\Request;
use Modules\Option\Transformers\OptionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Variation\Transformers\VariationResource;

class ProductEditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'google_product_category_path' => $this->google_product_category_path,
            'list_variants_separately' => (bool) $this->list_variants_separately,
            'brand_id' => $this->brand_id ?? '',
            'sale_unit_id' => $this->sale_unit_id ?? '',
            'unit_suffix' => $this->saleUnit ? $this->saleUnit->getDisplaySuffix() : '',
            'unit_min' => $this->saleUnit ? (float) $this->saleUnit->min : 0,
            'unit_step' => $this->saleUnit ? (float) $this->saleUnit->step : 1,
            'unit_decimal' => $this->saleUnit ? (bool) $this->saleUnit->is_decimal_stock : false,
            'categories' => $this->categories->pluck('id'),
            'primary_category_id' => $this->primary_category_id,
            'tags' => $this->tags->pluck('id'),
            'attributes' => ProductAttributeResource::collection($this->attributes),
            'variations' => VariationResource::collection($this->variations()->orderBy('position')->get()),
            'options' => OptionResource::collection($this->options),
            'variants' => ProductVariantResource::collection($this->variants()->withoutGlobalScope('active')->orderBy('position')->get()),
            'media' => collect([])
                ->concat($this->filterFiles('base_image')->get()->map->only('id', 'path'))
                ->concat($this->filterFiles('additional_images')->get()->map->only('id', 'path'))
                ->values()
                ->all(),
            'product_media' => $this->productMedia()->orderBy('position')->get(['id','product_id','variant_id','type','path','poster','position','is_active']),
            'price' => $this->price?->convertToCurrentCurrency()->amount(),
            'tax_class_id' => $this->tax_class_id ?? '',
            'sku' => $this->sku,
            'manage_stock' => $this->manage_stock,
            'qty' => $this->qty,
            'in_stock' => $this->in_stock,
            'special_price_type' => $this->special_price_type,
            'special_price' => $this->special_price?->convertToCurrentCurrency()->amount(),
            'new_from' => $this->new_from,
            'new_to' => $this->new_to,
            'up_sells' => $this->upSellProducts()->orderByPivot('created_at', 'asc')->pluck('id'),
            'cross_sells' => $this->crossSellProducts()->orderByPivot('created_at', 'asc')->pluck('id'),
            'related_products' => $this->relatedProducts()->orderByPivot('created_at', 'asc')->pluck('id'),
            'special_price_start' => $this->special_price_start,
            'special_price_end' => $this->special_price_end,
            'meta' => [
                'meta_title' => $this->meta->meta_title,
                'meta_description' => $this->meta->meta_description,
            ],
            'downloads' => $this->filterFiles('downloads')->get()->map->only('id', 'filename'),
            'is_virtual' => $this->is_virtual,
            'is_active' => $this->is_active,
        ];
    }
}

<?php

namespace Modules\Product\Listeners;

use Modules\Product\Entities\Product;

class SaveProductVariants
{
    /**
     * Handle the event.
     *
     * @param Product $product
     *
     * @return void
     */
    public function handle($product)
    {
        if (! request()->has('variants')) {
            return;
        }

        $ids = $this->getDeleteCandidates($product);

        if ($ids->isNotEmpty()) {
            $product->variants()
                ->withoutGlobalScope('active')
                ->whereIn('id', $ids->all())
                ->forceDelete();
        }

        $this->saveVariants($product);
    }


    private function getDeleteCandidates($product)
    {
        return $product
            ->variants()
            ->withoutGlobalScope('active')
            ->pluck('id')
            ->diff(array_pluck($this->variants(), 'id'));
    }


    private function variants()
    {
        return request('variants', []);
    }


    private function saveVariants($product)
    {
        $variants = $this->variants();
        $counter = 0;

        foreach ($variants as $attributes) {
            $attributes['position'] = ++$counter;
            $product->variants()->withoutGlobalScope('active')->updateOrCreate(['id' => $attributes['id'] ?? null], $attributes);
        }
    }
}

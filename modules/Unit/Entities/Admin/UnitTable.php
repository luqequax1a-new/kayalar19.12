<?php

namespace Modules\Unit\Entities\Admin;

use Modules\Admin\Ui\AdminTable;

class UnitTable extends AdminTable
{
    /**
     * Make table response for the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('name', function ($unit) {
                return e($unit->name);
            })
            ->addColumn('short_suffix', function ($unit) {
                return $unit->short_suffix ?: '-';
            })
            ->addColumn('min', function ($unit) {
                return $unit->min;
            })
            ->addColumn('step', function ($unit) {
                return $unit->step;
            })
            ->addColumn('default_qty', function ($unit) {
                return $unit->default_qty;
            })
            ->addColumn('products_count', function ($unit) {
                return \Modules\Product\Entities\Product::where('sale_unit_id', $unit->id)->count();
            })
            ->addColumn('created_at', function ($unit) {
                return $unit->created_at->toIso8601String();
            });
    }
}

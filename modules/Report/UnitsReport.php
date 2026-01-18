<?php

namespace Modules\Report;

use Modules\Unit\Entities\Unit;

class UnitsReport extends Report
{
    protected $filters = [];


    protected function view()
    {
        return 'report::admin.reports.units_report.index';
    }


    protected function query()
    {
        return Unit::select('id', 'name')
            ->withCount('products')
            ->when(request()->filled('unit'), function ($query) {
                $query->where('name', 'like', request('unit') . '%');
            })
            ->when(request()->filled('products_count_min'), function ($query) {
                $query->having('products_count', '>=', request('products_count_min'));
            })
            ->when(request()->filled('products_count_max'), function ($query) {
                $query->having('products_count', '<=', request('products_count_max'));
            })
            ->orderByDesc('products_count');
    }
}

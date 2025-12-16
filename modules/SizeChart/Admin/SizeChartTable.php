<?php

namespace Modules\SizeChart\Admin;

use Illuminate\Http\JsonResponse;
use Modules\Admin\Ui\AdminTable;
use Modules\SizeChart\Entities\SizeChart;
use Yajra\DataTables\Exceptions\Exception;

class SizeChartTable extends AdminTable
{
    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('title', function (SizeChart $sizeChart) {
                return $sizeChart->title;
            });
    }
}

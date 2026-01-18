<?php

namespace Modules\Question\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;

class QuestionTable extends AdminTable
{
    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->editColumn('product', function ($question) {
                return $question->product->name;
            })
            ->editColumn('status', function ($question) {
                return $question->is_approved
                    ? '<span class="badge badge-success">' . trans('admin::admin.table.approved') . '</span>'
                    : '<span class="badge badge-warning">' . trans('admin::admin.table.pending') . '</span>';
            })
            ->editColumn('answered', function ($question) {
                return $question->answer
                    ? '<span class="badge badge-success">Yanıtlandı</span>'
                    : '<span class="badge badge-danger">Yanıt Bekliyor</span>';
            });
    }
}

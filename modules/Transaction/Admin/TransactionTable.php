<?php

namespace Modules\Transaction\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Modules\Order\Entities\Order;

class TransactionTable extends AdminTable
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $rawColumns = ['order_id'];


    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('order_id', function ($transaction) {
                $orderUrl = route('admin.orders.show', $transaction->order_id);
                $order = Order::query()->select(['id', 'order_number'])->find($transaction->order_id);
                $label = $order ? e($order->displayOrderNumber()) : e((string) $transaction->order_id);

                return "<a href='{$orderUrl}'>{$label}</a>";
            });
    }
}

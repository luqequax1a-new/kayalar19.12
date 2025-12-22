<?php

namespace Modules\Order\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Exceptions\Exception;

class OrderTable extends AdminTable
{
    protected array $rawColumns = ['actions', 'status', 'created'];
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $defaultRawColumns = [
        'status',
    ];

    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('order_no', function ($order) {
                return $order->displayOrderNumber();
            })
            ->addColumn('customer_name', function ($order) {
                return $order->customer_full_name;
            })
            ->editColumn('payment_method', function ($order) {
                return $order->payment_method;
            })
            ->editColumn('total', function ($order) {
                return $order->total->format();
            })
            ->editColumn('status', function ($order) {
                return '<span class="badge ' . order_status_badge_class($order->status) . '">' . $order->status() . '</span>';
            })
            ->editColumn('created', function ($order) {
                $date = optional($order->created_at)->format('d.m.Y');
                $time = optional($order->created_at)->format('H:i');
                return "<div class='created-cell'><div>" . e($date) . "</div><div>" . e($time) . "</div></div>";
            })
            ->addColumn('actions', function ($order) {
                $url = route('admin.orders.show', $order->id);
                $orderNo = e($order->displayOrderNumber());
                return "<a href='{$url}' class='action-edit' title='Düzenle ({$orderNo})' data-toggle='tooltip' aria-label='Edit'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M4 20H20' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M16.44 3.56006L20.44 7.56006' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M14.02 5.98999L6.91 13.1C6.52 13.49 6.15 14.25 6.07 14.81L5.64 17.83C5.45 19.08 6.42 20.04 7.67 19.86L10.69 19.43C11.25 19.35 12.01 18.98 12.41 18.59L19.52 11.48' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                        </svg>
                    </a>";
            });
    }
}

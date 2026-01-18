<?php

namespace Modules\Report;

use Modules\Order\Entities\Order;
use Modules\Payment\Facades\Gateway;

class PaymentReport extends Report
{
    protected $date = 'orders.created_at';


    protected function view()
    {
        return 'report::admin.reports.payment_report.index';
    }


    protected function data()
    {
        $paymentMethods = Order::query()
            ->select('payment_method')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method')
            ->mapWithKeys(function ($method) {
                $label = Gateway::get($method)->label ?? $method;

                return [$method => (object) ['label' => $label]];
            });

        return [
            'paymentMethods' => $paymentMethods,
        ];
    }


    protected function query()
    {
        return Order::select('payment_method')
            ->selectRaw('MIN(orders.created_at) as start_date')
            ->selectRaw('MAX(orders.created_at) as end_date')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(orders.total) as total')
            ->when(request()->has('payment_method'), function ($query) {
                $query->where('payment_method', request('payment_method'));
            })
            ->groupBy('payment_method');
    }
}

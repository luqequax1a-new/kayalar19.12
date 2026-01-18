<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Order\Entities\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Display the order tracking form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('storefront::public.account.orders.track');
    }

    /**
     * Display the tracking results.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'email' => 'required|email',
        ]);

        $orderId = trim($request->order_id);
        $email = trim($request->email);

        $order = Order::with(['shippingAddress', 'shippingSnapshot'])
            ->where(function ($query) use ($orderId) {
                $query->where('order_number', $orderId)
                      ->orWhere('id', $orderId);
            })->where('customer_email', $email)->first();

        if (!$order) {
            return back()->withInput()->with('error', trans('storefront::account.orders.order_not_found'));
        }

        return view('storefront::public.account.orders.track', [
            'order' => $order,
        ]);
    }
}

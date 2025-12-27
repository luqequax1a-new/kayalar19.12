<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Order\Entities\Order;
use Modules\Checkout\Events\OrderPlaced;

class PaytrPaymentController extends Controller
{
    public function callback(Request $request)
    {
        $merchant_key = setting('paytr_merchant_key');
        $merchant_salt = setting('paytr_merchant_salt');

        $hash = $request->hash;
        $merchant_oid = $request->merchant_oid;
        $status = $request->status;
        $total_amount = $request->total_amount;

        $hash_str = $merchant_oid . $merchant_salt . $status . $total_amount;
        $calculated_hash = base64_encode(hash_hmac('sha256', $hash_str, $merchant_key, true));

        if ($calculated_hash !== $hash) {
            return 'PAYTR setting hash error';
        }

        if ($status === 'success') {
            $order = Order::findOrFail($merchant_oid);
            
            if ($order->status === Order::PENDING_PAYMENT || $order->status === Order::PENDING) {
                // If the order is not yet completed/paid
                $response = new \Modules\Payment\Responses\PaytrResponse($order);
                $order->storeTransaction($response);
                
                event(new OrderPlaced($order));
            }
            
            return 'OK';
        }

        return 'OK'; // Even if failed, PayTR expects OK so it stops retrying
    }
}

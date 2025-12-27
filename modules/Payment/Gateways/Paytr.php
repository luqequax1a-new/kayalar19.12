<?php

namespace Modules\Payment\Gateways;

use Exception;
use Illuminate\Http\Request;
use Modules\Order\Entities\Order;
use Modules\Payment\GatewayInterface;
use Modules\Payment\Responses\PaytrResponse;

class Paytr implements GatewayInterface
{
    public $label;
    public $description;

    public function __construct()
    {
        $this->label = setting('paytr_label');
        $this->description = setting('paytr_description');
    }

    public function purchase(Order $order, Request $request)
    {
        $order->load(['products', 'taxes', 'billingSnapshot', 'shippingSnapshot', 'billingAddress', 'shippingAddress']);

        $merchant_id = setting('paytr_merchant_id');
        $merchant_key = setting('paytr_merchant_key');
        $merchant_salt = setting('paytr_merchant_salt');
        
        $email = $order->customer_email;
        $payment_amount = (int) (round($order->total->convertToCurrentCurrency()->amount(), 2) * 100);
        $merchant_oid = (string) $order->id;
        $user_name = $order->customer_full_name;
        
        $shipping = $order->shippingSnapshot ?? $order->shippingAddress;
        $user_address = $shipping ? implode(', ', array_filter([
            $shipping->address_line ?? $shipping->address_1,
            $shipping->district ?? $shipping->state,
            $shipping->city,
            $shipping->country
        ])) : 'Address';

        $user_phone = $order->customer_phone;
        
        $merchant_ok_url = route('checkout.complete.store', ['orderId' => $order->id, 'paymentMethod' => 'paytr']);
        $merchant_fail_url = route('checkout.payment_canceled.store', ['orderId' => $order->id, 'paymentMethod' => 'paytr']);
        
        $user_basket = base64_encode(json_encode($this->prepareBasket($order)));
        
        $user_ip = $request->ip();
        if ($user_ip === '::1' || $user_ip === '127.0.0.1' || !$user_ip) {
            $user_ip = '88.238.60.177';
        }

        $timeout_limit = "30";
        $debug_on = setting('paytr_test_mode') ? 1 : 0;
        $test_mode = setting('paytr_test_mode') ? 1 : 0;
        $no_installment = 0;
        $max_installment = 0;
        $currency = $order->currency; // TRY, USD, etc.

        $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $user_basket . $no_installment . $max_installment . $currency . $test_mode;
        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));

        $post_vals = [
            'merchant_id' => $merchant_id,
            'user_ip' => $user_ip,
            'merchant_oid' => $merchant_oid,
            'email' => $email,
            'payment_amount' => $payment_amount,
            'paytr_token' => $paytr_token,
            'user_basket' => $user_basket,
            'debug_on' => $debug_on,
            'no_installment' => $no_installment,
            'max_installment' => $max_installment,
            'user_name' => $user_name,
            'user_address' => $user_address,
            'user_phone' => $user_phone,
            'merchant_ok_url' => $merchant_ok_url,
            'merchant_fail_url' => $merchant_fail_url,
            'timeout_limit' => $timeout_limit,
            'currency' => $currency,
            'test_mode' => $test_mode,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("PayTR Error: " . curl_error($ch));
        }

        curl_close($ch);

        $result = json_decode($result, 1);

        if ($result['status'] === 'success') {
            return new PaytrResponse($order, $result['token']);
        }

        throw new Exception($result['failed_reason'] ?? 'PayTR token error.');
    }

    public function complete(Order $order)
    {
        return new PaytrResponse($order, request('token'));
    }

    private function prepareBasket(Order $order): array
    {
        $basket = [];
        $total_in_basket = 0;

        foreach ($order->products as $orderProduct) {
            $name = $orderProduct->product_name;
            $suffix = $orderProduct->unit_short_suffix;
            if ($suffix && strtolower($suffix) !== 'adet') {
                $name .= ' (' . $suffix . ')';
            }
            
            $price = round($orderProduct->unit_price->convertToCurrentCurrency()->amount(), 2);
            $qty = (float) $orderProduct->qty;
            
            $basket[] = [$name, (string) $price, (string) $qty];
            $total_in_basket += ($price * $qty);
        }

        if ($order->shipping_cost->amount() > 0) {
            $price = round($order->shipping_cost->convertToCurrentCurrency()->amount(), 2);
            $basket[] = [trans('storefront::checkout.shipping_cost'), (string) $price, "1"];
            $total_in_basket += $price;
        }

        foreach ($order->taxes as $tax) {
            $price = round($tax->order_tax->amount->convertToCurrentCurrency()->amount(), 2);
            $basket[] = [$tax->name, (string) $price, "1"];
            $total_in_basket += $price;
        }

        if ($order->discount->amount() > 0) {
            $price = round($order->discount->convertToCurrentCurrency()->amount(), 2);
            $basket[] = [trans('storefront::checkout.discount'), (string) -$price, "1"];
            $total_in_basket -= $price;
        }

        // Final Adjustment to match payment_amount exactly if there's any float precision diff
        $order_total = round($order->total->convertToCurrentCurrency()->amount(), 2);
        $diff = round($order_total - $total_in_basket, 2);
        
        if ($diff != 0) {
            $basket[] = ['Adjustment', (string) $diff, "1"];
        }

        return $basket;
    }
}

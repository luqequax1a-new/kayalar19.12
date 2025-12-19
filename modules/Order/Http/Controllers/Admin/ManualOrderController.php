<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Modules\Cart\Facades\Cart;
use Modules\Order\Entities\Order;
use Modules\User\Entities\User;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Address\Entities\Address;
use Modules\Checkout\Services\OrderService as CheckoutOrderService;
use Modules\Order\Http\Requests\ManualOrderRequest;

class ManualOrderController
{
    public function create()
    {
        return view('order::admin.manual_orders.create');
    }

    public function store(ManualOrderRequest $request, \FleetCart\Services\AdminManualCartService $manualCartService)
    {
        $customer = User::findOrFail($request->input('customer_id'));

        $manualCartService->getCartFromRequest($request);
        $cart = $manualCartService->cart();

        $shippingAddressId = $request->input('shipping_address_id');
        $shippingAddress = $shippingAddressId ? Address::find($shippingAddressId) : null;
        $billingAddressId = $request->input('billing_address_id');
        $billingAddress = $billingAddressId ? Address::find($billingAddressId) : null;

        $manualCartService->calculateTotals($customer, $shippingAddress, $request->input('shipping_method'));

        $payload = $this->buildOrderPayloadFromRequest($request, $customer, $shippingAddress, $billingAddress);

        $order = Order::create($payload);

        $cart->items()->each(function ($cartItem) use ($order) {
            $order->storeProducts($cartItem);
            $order->storeDownloads($cartItem);
        });

        $paymentMode = $request->input('payment_mode');
        if ($paymentMode === 'manual_paid') {
            $order->forceFill(['payment_status' => 'paid', 'payment_method' => 'Bank Transfer'])->save();
            $order->transitionTo(Order::PROCESSING);
            $this->sendToGeliverIfEnabled($order);
            $redirectMessage = 'Manual order created successfully.';
        } elseif ($paymentMode === 'payment_link') {
            $order->forceFill(['payment_status' => 'pending', 'payment_method' => 'Payment Link'])->save();
            $order->transitionTo(Order::PENDING_PAYMENT);
            $this->sendToGeliverIfEnabled($order);
            $link = URL::signedRoute('checkout.payment_link.show', ['orderId' => $order->id]);
            $redirectMessage = 'Payment link generated: ' . $link;
        } else {
            $order->forceFill(['payment_status' => 'pending', 'payment_method' => 'Manual'])->save();
            $order->transitionTo(Order::PENDING_PAYMENT);
            $this->sendToGeliverIfEnabled($order);
            $redirectMessage = 'Manual order created successfully.';
        }

        $cart->clear();

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', $redirectMessage);
    }

    private function sendToGeliverIfEnabled(Order $order): void
    {
        if ((bool) setting('geliver_enabled') !== true) {
            return;
        }
        if ($order->geliver_shipment_id) {
            return;
        }
        $paymentMethod = (string) $order->payment_method;
        $bankLabel = (string) setting('bank_transfer_label');
        if ($paymentMethod !== '' && (
            $paymentMethod === 'Bank Transfer' ||
            ($bankLabel !== '' && $paymentMethod === $bankLabel)
        )) {
            return;
        }
        try {
            app(\Modules\Geliver\Services\GeliverService::class)->sendOrderToGeliver($order);
        } catch (\Throwable $e) {
        }
    }

    public function saveDraft(Request $request, \FleetCart\Services\AdminManualCartService $manualCartService): JsonResponse
    {
        $manualCartService->getCartFromRequest($request);
        return response()->json(['success' => true]);
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $query = trim((string) $request->get('query', ''));

        $customers = User::query()
            ->when($query !== '', function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email', 'phone'])
            ->map(function ($c) {
                $c->name = trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) . ' — ' . ($c->email ?? '') . ' — ' . ($c->phone ?? '');
                return $c;
            });

        return response()->json($customers);
    }

    public function addresses(User $customer): JsonResponse
    {
        return response()->json($customer->addresses()->get());
    }

    public function storeAddress(User $customer, Request $request): JsonResponse
    {
        $address = $customer->addresses()->create([
            'first_name' => $request->input('first_name', $customer->first_name),
            'last_name' => $request->input('last_name', $customer->last_name),
            'address_1' => $request->input('address_1'),
            'address_2' => $request->input('address_2'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'zip' => $request->input('zip'),
            'country' => $request->input('country'),
            'phone' => $request->input('phone'),
        ]);

        return response()->json($address);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $query = trim((string) $request->get('query', ''));
        $categoryId = $request->has('category_id') ? (int) $request->get('category_id') : null;

        $products = Product::withoutGlobalScope('active')
            ->with(['translations', 'variants:id,product_id,name,sku,price,special_price,special_price_type,special_price_start,special_price_end,selling_price', 'files' => function($q){ $q->wherePivot('zone', 'base_image'); }])
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($w) use ($query) {
                    $w->whereTranslationLike('name', "%{$query}%")
                      ->orWhere('sku', 'like', "%{$query}%");
                });
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereHas('categories', function ($c) use ($categoryId) {
                    $c->where('id', $categoryId);
                });
            })
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name ?: $p->sku,
                    'image' => $p->base_image?->path,
                    'in_stock' => (bool) $p->in_stock,
                    'manage_stock' => (bool) $p->manage_stock,
                    'qty' => is_numeric($p->qty) ? (float) $p->qty : $p->qty,
                    'variants' => $p->variants->map(function($v){
                        return [
                            'id' => $v->id,
                            'name' => $v->name,
                            'sku' => $v->sku,
                            'image' => $v->base_image?->path,
                            'price' => $v->selling_price?->amount(),
                            'selling_price' => $v->selling_price?->amount(),
                            'original_price' => $v->price?->amount(),
                            'in_stock' => (bool) $v->in_stock,
                            'manage_stock' => (bool) $v->manage_stock,
                            'qty' => is_numeric($v->qty) ? (float) $v->qty : $v->qty,
                        ];
                    })->values()->all(),
                    'price' => $p->selling_price?->amount(),
                    'selling_price' => $p->selling_price?->amount(),
                    'original_price' => $p->price?->amount(),
                    'unit_step' => $p->unit_step,
                    'unit_min' => $p->unit_min,
                    'unit_decimal' => $p->unit_decimal,
                    'unit_suffix' => $p->unit_suffix,
                ];
            });

        return response()->json($products);
    }

    public function previewCart(Request $request, \FleetCart\Services\AdminManualCartService $manualCartService): JsonResponse
    {
        $manualCartService->getCartFromRequest($request);

        $customerId = (int) $request->input('customer_id');
        $shippingAddressId = $request->input('shipping_address_id');
        $shippingMethod = $request->input('shipping_method');
        $billingAddressId = $request->input('billing_address_id');
        $billingAddress = $billingAddressId ? \Modules\Address\Entities\Address::find($billingAddressId) : null;

        $customer = $customerId ? \Modules\User\Entities\User::find($customerId) : null;
        $shippingAddress = $shippingAddressId ? \Modules\Address\Entities\Address::find($shippingAddressId) : null;

        if ($customer) {
            $manualCartService->calculateTotals($customer, $shippingAddress, $billingAddress, $shippingMethod);
        }

        $cart = $manualCartService->cart();

        return response()->json([
            'cart' => $cart->toArray(),
            'summary' => [
                'items_count' => $cart->items()->count(),
                'sub_total' => $cart->subTotal()->amount(),
                'shipping_cost' => $cart->shippingCost()->amount(),
                'discount' => $cart->discount()->amount(),
                'tax' => $cart->tax()->amount(),
                'total' => $cart->total()->amount(),
            ],
        ]);
    }

    private function buildOrderPayloadFromRequest(Request $request, User $customer, ?Address $shippingAddress, ?Address $billingAddress = null): array
    {
        $billing = $request->input('billing', []);
        $shipping = $request->input('shipping', []);

        if (empty($billing) && $billingAddress) {
            $billing = [
                'first_name' => $billingAddress->first_name,
                'last_name' => $billingAddress->last_name,
                'address_1' => $billingAddress->address_1,
                'address_2' => $billingAddress->address_2,
                'city' => $billingAddress->city,
                'state' => $billingAddress->state,
                'zip' => $billingAddress->zip,
                'country' => $billingAddress->country,
                'phone' => $billingAddress->phone,
            ];
        }

        if (empty($shipping) && $shippingAddress) {
            $shipping = [
                'first_name' => $shippingAddress->first_name,
                'last_name' => $shippingAddress->last_name,
                'address_1' => $shippingAddress->address_1,
                'address_2' => $shippingAddress->address_2,
                'city' => $shippingAddress->city,
                'state' => $shippingAddress->state,
                'zip' => $shippingAddress->zip,
                'country' => $shippingAddress->country,
                'phone' => $shippingAddress->phone,
            ];
        }

        $billingFirstName = $billing['first_name'] ?? ($shipping['first_name'] ?? '');
        $billingLastName = $billing['last_name'] ?? ($shipping['last_name'] ?? '');
        $billingAddress1 = $billing['address_1'] ?? ($shipping['address_1'] ?? '');
        $billingCity = $billing['city'] ?? ($shipping['city'] ?? '');
        $billingState = $billing['state'] ?? ($shipping['state'] ?? '');
        $billingCountry = $billing['country'] ?? ($shipping['country'] ?? '');
        $billingZip = $billing['zip'] ?? ($shipping['zip'] ?? '');
        $billingPhone = $billing['phone'] ?? null;

        return [
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'customer_first_name' => $shipping['first_name'] ?? $billingFirstName,
            'customer_last_name' => $shipping['last_name'] ?? $billingLastName,
            'billing_first_name' => $billingFirstName,
            'billing_last_name' => $billingLastName,
            'billing_address_1' => $billingAddress1,
            'billing_address_2' => $billing['address_2'] ?? null,
            'billing_city' => $billingCity,
            'billing_state' => $billingState,
            'billing_zip' => $billingZip,
            'billing_country' => $billingCountry,
            'billing_phone' => $billingPhone,
            'invoice_title' => $request->input('invoice.title'),
            'invoice_tax_office' => $request->input('invoice.tax_office'),
            'invoice_tax_number' => $request->input('invoice.tax_number'),
            'shipping_first_name' => $shipping['first_name'] ?? $billingFirstName,
            'shipping_last_name' => $shipping['last_name'] ?? $billingLastName,
            'shipping_address_1' => $shipping['address_1'] ?? $billingAddress1,
            'shipping_address_2' => $shipping['address_2'] ?? null,
            'shipping_city' => $shipping['city'] ?? $billingCity,
            'shipping_state' => $shipping['state'] ?? $billingState,
            'shipping_zip' => $shipping['zip'] ?? $billingZip,
            'shipping_country' => $shipping['country'] ?? $billingCountry,
            'shipping_phone' => $shipping['phone'] ?? null,
            'sub_total' => Cart::subTotal()->amount(),
            'shipping_method' => Cart::shippingMethod()->name(),
            'shipping_cost' => Cart::shippingCost()->amount(),
            'coupon_id' => Cart::coupon()->id(),
            'discount' => Cart::discount()->amount(),
            'total' => Cart::total()->amount(),
            'payment_method' => $request->input('payment_method', 'Manual'),
            'currency' => currency(),
            'currency_rate' => \Modules\Currency\Entities\CurrencyRate::for(currency()),
            'locale' => locale(),
            'status' => Order::PENDING_PAYMENT,
            'note' => $request->input('payment_note'),
            'created_from' => 'admin_manual',
            'created_by_admin_id' => auth('admin')->id(),
        ];
    }
}

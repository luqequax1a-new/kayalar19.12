<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Modules\Address\Entities\Address;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\SharedCart;
use Modules\User\Entities\User;
use FleetCart\Services\AdminManualCartService;
use Illuminate\Http\JsonResponse;
use Modules\Shipping\Facades\ShippingMethod;
use Modules\Payment\Facades\Gateway;
use Modules\Shipping\SmartShippingCod;
use Modules\Shipping\Services\SmartShippingCalculator;
use Modules\Shipping\Method as ShippingMethodModel;
use Modules\Support\Money;

class CartLinkController
{
    public function cities(): JsonResponse
    {
        $raw = @file_get_contents(base_path('sehirler.json'));
        $data = $raw ? (json_decode($raw, true) ?: []) : [];

        $out = collect($data)->map(function ($row) {
            return [
                'id' => (int) ($row['sehir_id'] ?? 0),
                'name' => (string) ($row['sehir_adi'] ?? ''),
            ];
        })->filter(function ($row) {
            return ($row['id'] ?? 0) > 0 && ($row['name'] ?? '') !== '';
        })->values();

        return response()->json($out);
    }

    public function addresses(User $customer): JsonResponse
    {
        $addresses = Address::query()
            ->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
                    ->orWhere('user_id', $customer->id);
            })
            ->orderByDesc('id')
            ->get();

        return response()->json($addresses);
    }

    public function districts(Request $request): JsonResponse
    {
        $cityId = (int) $request->query('city_id');

        $raw = @file_get_contents(base_path('ilceler.json'));
        $data = $raw ? (json_decode($raw, true) ?: []) : [];

        $out = collect($data)
            ->when($cityId > 0, function ($q) use ($cityId) {
                return $q->filter(function ($row) use ($cityId) {
                    return (int) ($row['sehir_id'] ?? 0) === $cityId;
                });
            })
            ->map(function ($row) {
                return [
                    'id' => (int) ($row['ilce_id'] ?? 0),
                    'name' => (string) ($row['ilce_adi'] ?? ''),
                    'city_id' => (int) ($row['sehir_id'] ?? 0),
                ];
            })
            ->filter(function ($row) {
                return ($row['id'] ?? 0) > 0 && ($row['name'] ?? '') !== '';
            })
            ->values();

        return response()->json($out);
    }

    public function create()
    {
        $categories = \Modules\Category\Entities\Category::with('translations')
            ->orderBy('id', 'asc')
            ->get(['id'])
            ->map(function($c){
                return ['id' => $c->id, 'name' => $c->name];
            })
            ->values()
            ->all();

        return view('order::admin.cart_links.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required',
            'customer_id' => 'nullable|integer|exists:users,id',
        ]);

        $items = $validated['items'];
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }

        $items = collect($items)->map(function($i){
            return [
                'product_id' => (int)($i['product_id'] ?? 0),
                'variant_id' => isset($i['variant_id']) ? (int)$i['variant_id'] : null,
                'qty' => (float)($i['qty'] ?? 1),
                'options' => $i['options'] ?? [],
                'manual_unit_price' => isset($i['manual_unit_price']) && is_numeric($i['manual_unit_price'])
                    ? (float) $i['manual_unit_price']
                    : null,
            ];
        })->filter(function($i){
            return $i['product_id'] > 0 && $i['qty'] > 0;
        })->values()->all();

        $token = Str::random(32);

            $link = SharedCart::create([
                'token' => $token,
                'data' => [
                    'items' => $items,
                    'customer_id' => $validated['customer_id'] ?? null,
                ],
                'created_by_admin_id' => auth()->id(),
            ]);

        $url = URL::route('checkout.cart_link.show', ['token' => $token]);

        return back()->with('success', 'Cart link created: ' . $url)->with('cart_link_url', $url);
    }

    public function preview(Request $request, AdminManualCartService $manualCartService): JsonResponse
    {
        try {
            $manualCartService->getCartFromRequest($request);

            $shippingMethod = $request->input('shipping_method');
            $shippingCostOverride = null;
            if ($shippingMethod === 'free_shipping') {
                $shippingCostOverride = 0;
            }

            $paymentMethod = $request->input('payment_method');

            $customerId = $request->input('customer_id');
            $shippingAddressId = $request->input('shipping_address_id');
            $billingAddressId = $request->input('billing_address_id');

            $shippingAddress = null;
            $billingAddress = null;

            if ($customerId) {
                if ($shippingAddressId) {
                    $shippingAddress = Address::where('customer_id', $customerId)->find($shippingAddressId);
                }
                if ($billingAddressId) {
                    $billingAddress = Address::where('customer_id', $customerId)->find($billingAddressId);
                }
            } else {
                $shippingAddress = $this->addressObjectFromArray((array) $request->input('shipping', []));
                $billingAddress = $this->addressObjectFromArray((array) $request->input('billing', []));
            }

            $manualCartService->calculateTotals(null, $shippingAddress, $billingAddress, $shippingMethod, $shippingCostOverride);

            $cart = $manualCartService->cart();
            $subTotal = $cart->subTotal();

            // Checkout-like COD fee behavior (manual cart subtotal)
            $cart->removeCodFee();
            if ($paymentMethod === 'cod') {
                $codFee = SmartShippingCod::codFeeForSubtotal($subTotal);
                $cart->addCodFee($codFee);
            }

            $shippingMethods = ShippingMethod::all();

            // Checkout-like smart_shipping cost override based on manual cart subtotal
            $hasSmart = $shippingMethods->first(function ($m) {
                return ($m && property_exists($m, 'name')) ? $m->name === 'smart_shipping' : false;
            });
            if ((bool) setting('smart_shipping_enabled') && $hasSmart) {
                /** @var SmartShippingCalculator $calculator */
                $calculator = app(SmartShippingCalculator::class);
                $label = setting('smart_shipping_name') ?: 'Standard Shipping';
                $cost = $calculator->costForSubtotal($subTotal);
                $shippingMethods = $shippingMethods->map(function ($method) use ($label, $cost) {
                    if ($method->name !== 'smart_shipping') {
                        return $method;
                    }

                    return new ShippingMethodModel('smart_shipping', $label, $cost->amount());
                });
            }

            // Checkout-like free_shipping availability based on manual cart subtotal
            $min = (float) (setting('free_shipping_min_amount') ?? 0);
            $minMoney = $min > 0 ? Money::inDefaultCurrency($min) : null;
            $shippingMethods = $shippingMethods->filter(function ($method) use ($subTotal, $minMoney) {
                if ($method->name !== 'free_shipping') {
                    return true;
                }

                if ($minMoney === null) {
                    return true;
                }

                return $subTotal->greaterThanOrEqual($minMoney);
            });

            $cartArray = $cart->toArray();
            $cartArray['availableShippingMethods'] = $shippingMethods->mapWithKeys(function ($m) {
                return [
                    $m->name => [
                        'name' => $m->name,
                        'label' => $m->label,
                        'cost' => $m->cost,
                    ],
                ];
            });

            // Checkout-like: only enabled gateways should be shown.
            $allGateways = Gateway::all()->filter(function ($gateway, $code) {
                return (bool) setting("{$code}_enabled");
            });

            if ($allGateways->has('cod') && !SmartShippingCod::allowedForSubtotal($subTotal)) {
                $allGateways->forget('cod');
            }

            $paymentMethods = $allGateways->map(function ($gateway, $code) {
                return [
                    'code' => $code,
                    'label' => property_exists($gateway, 'label') ? $gateway->label : '',
                    'description' => property_exists($gateway, 'description') ? $gateway->description : '',
                    'instructions' => property_exists($gateway, 'instructions') ? $gateway->instructions : '',
                ];
            })->values();

            return response()->json([
                'cart' => $cartArray,
                'payment_methods' => $paymentMethods,
                'summary' => [
                    'items_count' => $cart->items()->count(),
                    'sub_total' => $cart->subTotal()->amount(),
                    'shipping_cost' => $cart->shippingCost()->amount(),
                    'cod_fee' => $cart->codFee()->amount(),
                    'cod_fee_display_mode' => (string) (setting('cod_fee_display_mode') ?: 'separate_line'),
                    'discount' => $cart->discount()->amount(),
                    'tax' => $cart->tax()->amount(),
                    'total' => $cart->total()->amount(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    public function createOrder(Request $request, AdminManualCartService $manualCartService)
    {
        try {
            $validated = $request->validate([
                'items' => ['required'],
                'customer_id' => ['nullable', 'integer', 'exists:users,id'],
                'customer_email' => ['nullable', 'email'],
                'customer_phone' => ['nullable', 'regex:/^\\+90\\d{10}$/'],
                'customer_first_name' => ['nullable', 'string'],
                'customer_last_name' => ['nullable', 'string'],
                'shipping_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
                'billing_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
                'shipping' => ['nullable', 'array'],
                'billing' => ['nullable', 'array'],
                'shipping_method' => ['required', 'string'],
                'payment_method' => ['required', 'string'],
                'payment_note' => ['nullable', 'string'],
                'invoice' => ['nullable', 'array'],
                'invoice.title' => ['nullable', 'string'],
                'invoice.tax_office' => ['nullable', 'string'],
                'invoice.tax_number' => ['nullable', 'string'],
            ]);

            if (empty($validated['customer_id'])) {
                $request->validate([
                    'customer_email' => ['required', 'email'],
                    'customer_phone' => ['required', 'regex:/^\\+90\\d{10}$/'],
                    'customer_first_name' => ['required', 'string'],
                    'customer_last_name' => ['required', 'string'],
                ]);
            } else {
                $request->validate([
                    'shipping_address_id' => ['required', 'integer', 'exists:addresses,id'],
                    'billing_address_id' => ['required', 'integer', 'exists:addresses,id'],
                ]);
            }

            $manualCartService->getCartFromRequest($request);

            $shippingMethod = $request->input('shipping_method');

            $customerId = $request->input('customer_id');
            $customer = $customerId ? User::find($customerId) : null;
            $shippingAddressId = $request->input('shipping_address_id');
            $billingAddressId = $request->input('billing_address_id');

            $shippingAddress = null;
            $billingAddress = null;

            if ($customerId) {
                if ($shippingAddressId) {
                    $shippingAddress = Address::where('customer_id', $customerId)->find($shippingAddressId);
                }
                if ($billingAddressId) {
                    $billingAddress = Address::where('customer_id', $customerId)->find($billingAddressId);
                }
            } else {
                $shippingAddress = $this->addressObjectFromArray((array) $request->input('shipping', []));
                $billingAddress = $this->addressObjectFromArray((array) $request->input('billing', []));
            }

            if ($customerId && (!$shippingAddress || !$billingAddress)) {
                return back()->withErrors(['shipping_address_id' => 'Adres bulunamadı.']);
            }

            $manualCartService->calculateTotals(null, $shippingAddress, $billingAddress, $shippingMethod);

            $cart = $manualCartService->cart();

            $billing = (array) $request->input('billing', []);
            $shipping = (array) $request->input('shipping', []);

            if ($customerId) {
                $shipping = [
                    'first_name' => $shippingAddress->first_name,
                    'last_name' => $shippingAddress->last_name,
                    'address_1' => $shippingAddress->address_1,
                    'address_2' => $shippingAddress->address_2,
                    'city' => $shippingAddress->city,
                    'state' => $shippingAddress->state,
                    'country' => $shippingAddress->country,
                    'phone' => $shippingAddress->phone,
                ];
                $billing = [
                    'first_name' => $billingAddress->first_name,
                    'last_name' => $billingAddress->last_name,
                    'address_1' => $billingAddress->address_1,
                    'address_2' => $billingAddress->address_2,
                    'city' => $billingAddress->city,
                    'state' => $billingAddress->state,
                    'country' => $billingAddress->country,
                    'phone' => $billingAddress->phone,
                ];
            }

            $billingFirstName = $billing['first_name'] ?? ($shipping['first_name'] ?? $request->input('customer_first_name', ''));
            $billingLastName = $billing['last_name'] ?? ($shipping['last_name'] ?? $request->input('customer_last_name', ''));
            $billingAddress1 = $billing['address_1'] ?? ($shipping['address_1'] ?? '');
            $billingCity = $billing['city'] ?? ($shipping['city'] ?? '');
            $billingState = $billing['state'] ?? ($shipping['state'] ?? '');
            $billingCountry = $billing['country'] ?? ($shipping['country'] ?? 'TR');
            $billingZip = '';
            $billingPhone = $billing['phone'] ?? ($shipping['phone'] ?? $request->input('customer_phone'));

            $paymentCode = (string) $request->input('payment_method');
            $paymentMethod = $paymentCode;

            $order = Order::create([
            'customer_id' => $customerId,
            'customer_email' => $customerId ? (string) ($customer?->email ?? '') : (string) $request->input('customer_email'),
            'customer_phone' => $customerId ? (string) ($customer?->phone ?? '') : (string) $request->input('customer_phone'),
            'customer_first_name' => $shipping['first_name'] ?? $request->input('customer_first_name', $billingFirstName),
            'customer_last_name' => $shipping['last_name'] ?? $request->input('customer_last_name', $billingLastName),
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
            'shipping_zip' => $billingZip,
            'shipping_country' => $shipping['country'] ?? $billingCountry,
            'shipping_phone' => $shipping['phone'] ?? $billingPhone,
            'shipping_address_id' => $shippingAddressId,
            'billing_address_id' => $billingAddressId,
            'sub_total' => $cart->subTotal()->amount(),
            'shipping_method' => $cart->shippingMethod()->name(),
            'shipping_cost' => $cart->shippingCost()->amount(),
            'coupon_id' => $cart->coupon()->id(),
            'discount' => $cart->discount()->amount(),
            'total' => $cart->total()->amount(),
            'payment_method' => $paymentMethod,
            'currency' => currency(),
            'currency_rate' => \Modules\Currency\Entities\CurrencyRate::for(currency()),
            'locale' => locale(),
            'status' => Order::PENDING_PAYMENT,
            'note' => $request->input('payment_note'),
            'created_from' => 'admin_manual',
            'created_by_admin_id' => auth('admin')->id(),
            ]);

        $cart->items()->each(function ($cartItem) use ($order) {
            $order->storeProducts($cartItem);
            $order->storeDownloads($cartItem);
        });

        $cart->reduceStock();

            $order->transitionTo(Order::PENDING_PAYMENT);

            $cart->clear();

            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'Sipariş oluşturuldu.');
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (config('app.debug')) {
                $msg .= ' @ ' . $e->getFile() . ':' . $e->getLine();
            }

            return back()->withErrors(['order' => $msg])->withInput();
        }
    }

    private function addressObjectFromArray(array $data)
    {
        $data = is_array($data) ? $data : [];

        if (empty($data)) {
            return null;
        }

        return (object) [
            'country' => $data['country'] ?? null,
            'state' => $data['state'] ?? null,
            'zip' => $data['zip'] ?? null,
        ];
    }
}

<?php

namespace Modules\Checkout\Services;

use Modules\Cart\CartTax;
use Modules\Cart\CartItem;
use Modules\Cart\Facades\Cart;
use Modules\Order\Entities\Order;
use Modules\Coupon\Entities\Coupon;
use Modules\Address\Entities\Address;
use Modules\FlashSale\Entities\FlashSale;
use Modules\Currency\Entities\CurrencyRate;
use Modules\Address\Entities\DefaultAddress;
use Modules\Shipping\Facades\ShippingMethod;
use Modules\Shipping\SmartShippingCod;
use Modules\Shipping\Services\SmartShippingCalculator;
use Modules\Shipping\Method as ShippingMethodModel;
use Modules\Checkout\Exceptions\CheckoutException;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function create($request)
    {
        $this->mergeShippingAddress($request);
        $customer = auth()->user();
        [$shippingAddress, $billingAddress] = $this->resolveAndPersistAddresses($request, $customer);
        $request->merge([
            'shipping_address_id' => $shippingAddress?->id,
            'billing_address_id' => $billingAddress?->id,
        ]);
        $this->addShippingMethodToCart($request);

        if ($request->payment_method === 'cod' && !SmartShippingCod::allowedForCurrentCart()) {
            throw new CheckoutException(trans('checkout::messages.no_shipping_method'));
        }

        return tap($this->store($request), function ($order) use ($request) {
            $this->snapshotOrderAddresses($order, $request);
            $this->storeOrderProducts($order);
            $this->storeOrderDownloads($order);
            $this->storeFlashSaleProductOrders($order);
            $this->incrementCouponUsage($order);
            $this->markCouponAsRedeemed($order);
            $this->markCartAsRecovered($order);
            $this->attachTaxes($order);
            $this->reduceStock();
        });
    }


    public function reduceStock()
    {
        Cart::reduceStock();
    }


    public function delete(Order $order)
    {
        $order->delete();

        Cart::restoreStock();
    }


    private function mergeShippingAddress($request)
    {
        // UI flag means "billing is different".
        // When billing is NOT different, mirror billing from shipping.
        // Never overwrite shipping payload, otherwise guest orders can lose shipping address data.
        if (!$request->boolean('ship_to_a_different_address') && !$request->boolean('has_different_billing')) {
            $request->merge([
                'billing' => $request->shipping,
            ]);
        }
    }


    private function saveAddress($request)
    {
        if (auth()->guest()) {
            return;
        }

        if ($request->newBillingAddress) {
            $billingPayload = $this->extractAddress($request->billing, $request->shipping ?? []);
            $billingPayload['invoice_title'] = $request->invoice['title'] ?? null;
            $billingPayload['invoice_tax_office'] = $request->invoice['tax_office'] ?? null;
            $billingPayload['invoice_tax_number'] = $request->invoice['tax_number'] ?? null;

            $address = auth()
                ->user()
                ->addresses()
                ->create($billingPayload);

            $this->makeDefaultAddress($address);
        }

        if (!$request->ship_to_a_different_address && $request->newShippingAddress && !$request->newBillingAddress) {
            $billingPayload = $this->extractAddress($request->shipping, $request->billing ?? []);
            $billingPayload['invoice_title'] = $request->invoice['title'] ?? null;
            $billingPayload['invoice_tax_office'] = $request->invoice['tax_office'] ?? null;
            $billingPayload['invoice_tax_number'] = $request->invoice['tax_number'] ?? null;

            $exists = auth()
                ->user()
                ->addresses()
                ->where($billingPayload)
                ->exists();

            if (!$exists) {
                $address = auth()
                    ->user()
                    ->addresses()
                    ->create($billingPayload);

                $this->makeDefaultAddress($address);
            }
        }

        if ($request->ship_to_a_different_address && $request->newShippingAddress) {
            auth()
                ->user()
                ->addresses()
                ->create($this->extractAddress($request->shipping));
        }
    }


    private function extractAddress($data, $fallback = [])
    {
        return [
            'type' => $data['type'] ?? null,
            'customer_id' => auth()->id(),
            'user_id' => auth()->id(),
            'first_name' => $data['first_name'] ?? ($fallback['first_name'] ?? ''),
            'last_name' => $data['last_name'] ?? ($fallback['last_name'] ?? ''),
            'company_name' => $data['company_name'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'tax_office' => $data['tax_office'] ?? null,
            'address_1' => $data['address_1'] ?? ($fallback['address_1'] ?? ''),
            'address_2' => $data['address_2'] ?? null,
            'address_line' => $data['address_line'] ?? ($data['address_1'] ?? ($fallback['address_1'] ?? '')),
            'city' => $data['city'] ?? ($fallback['city'] ?? ''),
            'state' => $data['state'] ?? ($fallback['state'] ?? ''),
            'zip' => $data['zip'] ?? ($fallback['zip'] ?? ''),
            'country' => $data['country'] ?? ($fallback['country'] ?? ''),
            'city_id' => $data['city_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'phone' => $data['phone'] ?? ($fallback['phone'] ?? null),
            'invoice_title' => $data['invoice_title'] ?? null,
            'invoice_tax_office' => $data['invoice_tax_office'] ?? null,
            'invoice_tax_number' => $data['invoice_tax_number'] ?? null,
            'billing_email' => $data['billing_email'] ?? null,
        ];
    }


    private function makeDefaultAddress(Address $address)
    {
        if (
            auth()
                ->user()
                ->addresses()
                ->count() > 1
        ) {
            return;
        }

        DefaultAddress::updateOrCreate(
            ['customer_id' => auth()->id()],
            [
                'default_shipping_address_id' => $address->id,
                'default_billing_address_id' => $address->id,
            ]
        );
    }


    private function addShippingMethodToCart($request)
    {
        if (Cart::allItemsAreVirtual()) {
            return;
        }

        $shippingName = $request->shipping_method;

        if ($shippingName === 'smart_shipping') {
            /** @var SmartShippingCalculator $calculator */
            $calculator = app(SmartShippingCalculator::class);

            $label = setting('smart_shipping_name') ?: 'Standard Shipping';
            $cost = $calculator->costForCurrentCart();

            Cart::addShippingMethod(new ShippingMethodModel('smart_shipping', $label, $cost->amount()));

            return;
        }

        Cart::addShippingMethod(ShippingMethod::get($shippingName));
    }


    private function store($request)
    {
        $billing = $request->input('billing', []);
        $shipping = $request->input('shipping', []);

        $attribution = $this->orderAttributionFromSession();
        $trafficSource = $this->classifyTrafficSource($attribution);

        return Order::create([
            'customer_id' => auth()->id(),
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_first_name' => $shipping['first_name'] ?? ($billing['first_name'] ?? ''),
            'customer_last_name' => $shipping['last_name'] ?? ($billing['last_name'] ?? ''),
            'shipping_address_id' => $request->shipping_address_id,
            'billing_address_id' => $request->billing_address_id,
            'sub_total' => Cart::subTotal()->amount(),
            'shipping_method' => Cart::shippingMethod()->name(),
            'shipping_cost' => Cart::shippingCost()->amount(),
            'coupon_id' => Cart::coupon()->id(),
            'coupon_code' => Cart::coupon()->code(),
            'discount' => Cart::discount()->amount(),
            'total' => Cart::total()->amount(),
            'payment_method' => $request->payment_method,
            'currency' => currency(),
            'currency_rate' => CurrencyRate::for(currency()),
            'locale' => locale(),
            'status' => Order::PENDING_PAYMENT,
            'note' => $request->order_note,

            'traffic_source' => $trafficSource,
            'utm_source' => $attribution['utm_source'] ?? null,
            'utm_medium' => $attribution['utm_medium'] ?? null,
            'utm_campaign' => $attribution['utm_campaign'] ?? null,
            'utm_term' => $attribution['utm_term'] ?? null,
            'utm_content' => $attribution['utm_content'] ?? null,
            'referrer_url' => $attribution['referrer_url'] ?? null,
            'landing_url' => $attribution['landing_url'] ?? null,
            'click_id_gclid' => $attribution['click_id_gclid'] ?? null,
            'click_id_fbclid' => $attribution['click_id_fbclid'] ?? null,
        ]);
    }

    private function orderAttributionFromSession(): array
    {
        try {
            $session = request()?->session();
            if (!$session) {
                return [];
            }

            $data = $session->get('order_attribution');

            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function classifyTrafficSource(array $a): ?string
    {
        $utmSource = strtolower(trim((string) ($a['utm_source'] ?? '')));
        $utmMedium = strtolower(trim((string) ($a['utm_medium'] ?? '')));

        $gclid = trim((string) ($a['click_id_gclid'] ?? ''));
        $fbclid = trim((string) ($a['click_id_fbclid'] ?? ''));

        $ref = strtolower(trim((string) ($a['referrer_url'] ?? '')));

        $hasAnyUtm = $utmSource !== '' || $utmMedium !== '' || trim((string) ($a['utm_campaign'] ?? '')) !== '';

        $paidMediums = ['cpc', 'ppc', 'paid', 'google_ads'];

        if ($gclid !== '' || ($utmSource === 'google' && in_array($utmMedium, $paidMediums, true))) {
            return 'google_ads';
        }

        if ($fbclid !== '' || ($utmSource === 'facebook' && in_array($utmMedium, ['cpc', 'ppc', 'paid'], true))) {
            return 'facebook_ads';
        }

        if ($utmSource === 'instagram' && in_array($utmMedium, ['cpc', 'ppc', 'paid'], true)) {
            return 'instagram_ads';
        }

        if ($ref !== '' && str_contains($ref, 'etsy.com')) {
            return 'etsy';
        }

        if ($ref !== '' && str_contains($ref, 'google.') && $utmMedium === '') {
            return 'google_organic';
        }

        if (! $hasAnyUtm && $ref === '') {
            return 'direct';
        }

        if ($hasAnyUtm || $ref !== '') {
            return 'other';
        }

        return null;
    }

    private function resolveAndPersistAddresses($request, $customer = null): array
    {
        $shippingData = (array) $request->input('shipping', []);
        $billingData = (array) $request->input('billing', []);
        $hasDifferentBilling = $request->boolean('has_different_billing') || $request->boolean('ship_to_a_different_address');

        $invoice = (array) $request->input('invoice', []);
        if (! empty($invoice)) {
            $billingData['invoice_title'] = $billingData['invoice_title'] ?? ($invoice['title'] ?? null);
            $billingData['invoice_tax_office'] = $billingData['invoice_tax_office'] ?? ($invoice['tax_office'] ?? null);
            $billingData['invoice_tax_number'] = $billingData['invoice_tax_number'] ?? ($invoice['tax_number'] ?? null);

            $billingData['company_name'] = $billingData['company_name'] ?? ($invoice['title'] ?? null);
            $billingData['tax_office'] = $billingData['tax_office'] ?? ($invoice['tax_office'] ?? null);
            $billingData['tax_number'] = $billingData['tax_number'] ?? ($invoice['tax_number'] ?? null);
        }

        $shippingAddressId = $request->input('shipping_address_id') ?? $request->input('shippingAddressId');
        $billingAddressId = $request->input('billing_address_id') ?? $request->input('billingAddressId');

        if (auth()->guest()) {
            $shippingAddressId = null;
            $billingAddressId = null;
        }

        if ($shippingAddressId) {
            $shippingAddress = Address::where('customer_id', $customer?->id)
                ->where('id', (int) $shippingAddressId)
                ->firstOrFail();
        } else {
            $shippingAddress = $this->createAddressFromArray($shippingData, Address::TYPE_SHIPPING, $customer);
        }

        if ($hasDifferentBilling) {
            if ($billingAddressId) {
                $billingAddress = Address::where('customer_id', $customer?->id)
                    ->where('id', (int) $billingAddressId)
                    ->firstOrFail();
            } else {
                $billingAddress = $this->createAddressFromArray($billingData, Address::TYPE_BILLING, $customer);
            }
        } else {
            $billingAddress = $shippingAddress;
        }

        return [$shippingAddress, $billingAddress];
    }

    private function createAddressFromArray(array $data, string $type, $customer = null): Address
    {
        $customerId = $customer?->id ?? auth()->id();
        $cityIdRaw = $data['city_id'] ?? null;
        $districtIdRaw = $data['district_id'] ?? null;
        $cityId = is_numeric($cityIdRaw) ? (int) $cityIdRaw : null;
        $districtId = is_numeric($districtIdRaw) ? (int) $districtIdRaw : null;

        $payload = [
            'customer_id' => $customerId,
            'user_id' => $customerId,
            'type' => $type,

            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,

            'company_name' => $data['company_name'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'tax_office' => $data['tax_office'] ?? null,

            'phone' => $data['phone'] ?? null,
            'city_id' => $cityId,
            'district_id' => $districtId,
            'address_line' => $data['address_line'] ?? null,

            'address_1' => $data['address_line'] ?? '',
            'address_2' => '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'zip' => '',
            'country' => 'TR',
            'invoice_title' => $data['invoice_title'] ?? null,
            'invoice_tax_office' => $data['invoice_tax_office'] ?? null,
            'invoice_tax_number' => $data['invoice_tax_number'] ?? null,
            'billing_email' => $data['billing_email'] ?? null,
        ];

        if ($customerId === null) {
            return new Address($payload);
        }

        return Address::create($payload);
    }

    private function snapshotOrderAddresses(Order $order, $request): void
    {
        try {
            if (!\Schema::hasTable('order_addresses')) {
                return;
            }

            $now = now();

            $shipping = $order->shippingAddress;
            $billing = $order->billingAddress;

            $shippingData = (array) $request->input('shipping', []);
            $billingData = (array) $request->input('billing', []);
            $hasDifferentBilling = $request->boolean('ship_to_a_different_address') || $request->boolean('has_different_billing');
            if (! $hasDifferentBilling) {
                $billingData = $shippingData;
            }

            // Reset snapshots to prevent duplicates on retries.
            \DB::table('order_addresses')->where('order_id', $order->id)->delete();

            $resolveCityDistrict = function (array $data, ?Address $addr = null): array {
                if ($addr) {
                    return [
                        'city' => $addr->city_title ?? $addr->city,
                        'district' => $addr->district_title ?? $addr->state,
                    ];
                }

                $tmp = new Address([
                    'city_id' => $data['city_id'] ?? null,
                    'district_id' => $data['district_id'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'country' => $data['country'] ?? 'TR',
                ]);

                return [
                    'city' => $tmp->city_title ?? ($data['city'] ?? null),
                    'district' => $tmp->district_title ?? ($data['state'] ?? null),
                ];
            };

            $shipLoc = $resolveCityDistrict($shippingData, $shipping);
            \DB::table('order_addresses')->insert([
                'order_id' => $order->id,
                'type' => Address::TYPE_SHIPPING,
                'first_name' => $shipping?->first_name ?? ($shippingData['first_name'] ?? null),
                'last_name' => $shipping?->last_name ?? ($shippingData['last_name'] ?? null),
                'company_name' => $shipping?->company_name ?? ($shippingData['company_name'] ?? null),
                'tax_number' => $shipping?->tax_number ?? ($shippingData['tax_number'] ?? null),
                'tax_office' => $shipping?->tax_office ?? ($shippingData['tax_office'] ?? null),
                'phone' => $shipping?->phone ?? ($shippingData['phone'] ?? null) ?? ($order->customer_phone ?? null),
                'city' => $shipLoc['city'],
                'district' => $shipLoc['district'],
                'zip' => $shipping?->zip ?? ($shippingData['zip'] ?? null),
                'country' => $shipping?->country ?? ($shippingData['country'] ?? 'TR'),
                'address_line' => $shipping?->address_line ?? $shipping?->address_1 ?? ($shippingData['address_line'] ?? ($shippingData['address_1'] ?? null)),
                'address_2' => $shipping?->address_2 ?? ($shippingData['address_2'] ?? null),
                'billing_email' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $billLoc = $resolveCityDistrict($billingData, $billing);
            $billingCompany = $billing?->invoice_title ?: ($billing?->company_name ?? null);
            $billingTaxNo = $billing?->invoice_tax_number ?: ($billing?->tax_number ?? null);
            $billingTaxOffice = $billing?->invoice_tax_office ?: ($billing?->tax_office ?? null);

            \DB::table('order_addresses')->insert([
                'order_id' => $order->id,
                'type' => Address::TYPE_BILLING,
                'first_name' => $billing?->first_name ?? ($billingData['first_name'] ?? ($shippingData['first_name'] ?? null)),
                'last_name' => $billing?->last_name ?? ($billingData['last_name'] ?? ($shippingData['last_name'] ?? null)),
                'company_name' => $billingCompany ?? ($billingData['invoice_title'] ?? ($billingData['company_name'] ?? null)),
                'tax_number' => $billingTaxNo ?? ($billingData['invoice_tax_number'] ?? ($billingData['tax_number'] ?? null)),
                'tax_office' => $billingTaxOffice ?? ($billingData['invoice_tax_office'] ?? ($billingData['tax_office'] ?? null)),
                'phone' => $billing?->phone ?? ($billingData['phone'] ?? null) ?? ($order->customer_phone ?? null),
                'city' => $billLoc['city'],
                'district' => $billLoc['district'],
                'zip' => $billing?->zip ?? ($billingData['zip'] ?? null),
                'country' => $billing?->country ?? ($billingData['country'] ?? 'TR'),
                'address_line' => $billing?->address_line ?? $billing?->address_1 ?? ($billingData['address_line'] ?? ($billingData['address_1'] ?? null)),
                'address_2' => $billing?->address_2 ?? ($billingData['address_2'] ?? null),
                'billing_email' => $billing?->billing_email ?? ($billingData['billing_email'] ?? null),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (\Throwable $e) {
            Log::channel('checkout')->error('checkout.order_address_snapshot_failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }


    private function storeOrderProducts(Order $order)
    {
        Cart::items()->each(function (CartItem $cartItem) use ($order) {
            $order->storeProducts($cartItem);
        });
    }


    private function storeOrderDownloads(Order $order)
    {
        Cart::items()->each(function (CartItem $cartItem) use ($order) {
            $order->storeDownloads($cartItem);
        });
    }


    private function storeFlashSaleProductOrders(Order $order)
    {
        Cart::items()->each(function (CartItem $cartItem) use ($order) {
            if (!FlashSale::contains($cartItem->product)) {
                return;
            }

            FlashSale::pivot($cartItem->product)
                ->orders()
                ->attach([
                    $cartItem->product->id => [
                        'order_id' => $order->id,
                        'qty' => $cartItem->qty,
                    ],
                ]);
        });
    }


    private function incrementCouponUsage()
    {
        Cart::coupon()->usedOnce();
    }

    private function markCouponAsRedeemed(Order $order): void
    {
        if (!$order->coupon_id) {
            return;
        }

        $coupon = Coupon::query()->withoutGlobalScope('active')->find($order->coupon_id);
        if (!$coupon) {
            return;
        }

        if (! is_null($coupon->usage_limit_per_coupon) && $coupon->usage_limit_per_coupon > 0) {
            $coupon->usage_limit_per_coupon = max(0, $coupon->usage_limit_per_coupon - 1);
        }

        $coupon->redeemed_order_id = $order->id;
        $coupon->redeemed_at = now();

        if ($coupon->is_review_coupon || $coupon->is_abandoned_cart_coupon) {
            $coupon->is_active = false;
        }

        $coupon->save();
    }


    private function markCartAsRecovered(Order $order)
    {
        $sessionId = session()->getId();
        \Modules\Cart\Entities\Cart::where('id', 'like', $sessionId . '%')
            ->update([
                'is_recovered' => true,
                'recovered_at' => now(),
                'order_id' => $order->id,
            ]);
    }


    private function attachTaxes(Order $order)
    {
        Cart::taxes()->each(function (CartTax $cartTax) use ($order) {
            $order->attachTax($cartTax);
        });
    }
}

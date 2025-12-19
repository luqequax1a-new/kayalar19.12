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
use Modules\Account\Entities\DefaultAddress;
use Modules\Shipping\Facades\ShippingMethod;
use Modules\Shipping\SmartShippingCod;
use Modules\Shipping\Services\SmartShippingCalculator;
use Modules\Shipping\Method as ShippingMethodModel;
use Modules\Checkout\Exceptions\CheckoutException;

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

        return tap($this->store($request), function ($order) {
            $this->snapshotOrderAddresses($order);
            $this->storeOrderProducts($order);
            $this->storeOrderDownloads($order);
            $this->storeFlashSaleProductOrders($order);
            $this->incrementCouponUsage($order);
            $this->markCouponAsRedeemed($order);
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
        $request->merge([
            'shipping' => $request->ship_to_a_different_address ? $request->shipping : $request->billing,
        ]);
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

        DefaultAddress::create([
            'address_id' => $address->id,
            'customer_id' => auth()->id(),
        ]);
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

        $billingFirstName = $billing['first_name'] ?? ($shipping['first_name'] ?? '');
        $billingLastName = $billing['last_name'] ?? ($shipping['last_name'] ?? '');
        $billingAddress1 = $billing['address_1'] ?? ($billing['address_line'] ?? ($shipping['address_1'] ?? ($shipping['address_line'] ?? '')));
        $billingCity = $billing['city'] ?? ($billing['city_id'] ?? ($shipping['city'] ?? ($shipping['city_id'] ?? '')));
        $billingState = $billing['state'] ?? ($billing['district_id'] ?? ($shipping['state'] ?? ($shipping['district_id'] ?? '')));
        $billingCountry = $billing['country'] ?? ($shipping['country'] ?? 'TR');
        $billingZip = $billing['zip'] ?? ($shipping['zip'] ?? '');
        $billingPhone = $billing['phone'] ?? ($shipping['phone'] ?? null);

        $attribution = $this->orderAttributionFromSession();
        $trafficSource = $this->classifyTrafficSource($attribution);

        return Order::create([
            'customer_id' => auth()->id(),
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
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
            'invoice_title' => $request->invoice['title'] ?? null,
            'invoice_tax_office' => $request->invoice['tax_office'] ?? null,
            'invoice_tax_number' => $request->invoice['tax_number'] ?? null,
            'shipping_first_name' => $shipping['first_name'] ?? '',
            'shipping_last_name' => $shipping['last_name'] ?? '',
            'shipping_address_1' => $shipping['address_1'] ?? ($shipping['address_line'] ?? ''),
            'shipping_address_2' => $shipping['address_2'] ?? null,
            'shipping_city' => $shipping['city'] ?? ($shipping['city_id'] ?? ''),
            'shipping_state' => $shipping['state'] ?? ($shipping['district_id'] ?? ''),
            'shipping_zip' => $shipping['zip'] ?? '',
            'shipping_country' => $shipping['country'] ?? 'TR',
            'shipping_phone' => $shipping['phone'] ?? null,
            'shipping_address_id' => $request->shipping_address_id,
            'billing_address_id' => $request->billing_address_id,
            'sub_total' => Cart::subTotal()->amount(),
            'shipping_method' => Cart::shippingMethod()->name(),
            'shipping_cost' => Cart::shippingCost()->amount(),
            'coupon_id' => Cart::coupon()->id(),
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

        $shippingAddressId = $request->input('shipping_address_id') ?? $request->input('shippingAddressId');
        $billingAddressId = $request->input('billing_address_id') ?? $request->input('billingAddressId');

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
            'invoice_title' => '',
            'invoice_tax_office' => '',
            'invoice_tax_number' => '',
        ];

        if ($customerId === null) {
            return new Address($payload);
        }

        return Address::create($payload);
    }

    private function snapshotOrderAddresses(Order $order): void
    {
        try {
            $shipping = $order->shippingAddress;
            $billing = $order->billingAddress;

            if ($shipping) {
                \DB::table('order_addresses')->insert([
                    'order_id' => $order->id,
                    'type' => Address::TYPE_SHIPPING,
                    'first_name' => $shipping->first_name,
                    'last_name' => $shipping->last_name,
                    'company_name' => $shipping->company_name,
                    'tax_number' => $shipping->tax_number,
                    'tax_office' => $shipping->tax_office,
                    'phone' => $shipping->phone,
                    'city' => $shipping->city,
                    'district' => $shipping->state,
                    'address_line' => $shipping->address_line ?? $shipping->address_1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($billing) {
                \DB::table('order_addresses')->insert([
                    'order_id' => $order->id,
                    'type' => Address::TYPE_BILLING,
                    'first_name' => $billing->first_name,
                    'last_name' => $billing->last_name,
                    'company_name' => $billing->company_name,
                    'tax_number' => $billing->tax_number,
                    'tax_office' => $billing->tax_office,
                    'phone' => $billing->phone,
                    'city' => $billing->city,
                    'district' => $billing->state,
                    'address_line' => $billing->address_line ?? $billing->address_1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
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

        if ($coupon->is_review_coupon) {
            $coupon->is_active = false;
        }

        $coupon->save();
    }


    private function attachTaxes(Order $order)
    {
        Cart::taxes()->each(function (CartTax $cartTax) use ($order) {
            $order->attachTax($cartTax);
        });
    }
}

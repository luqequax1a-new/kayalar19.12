<?php

namespace FleetCart\Services;

use Illuminate\Http\Request;
use Modules\Cart\Cart as StorefrontCart;
use Modules\Cart\Storages\Database as CartDatabaseStorage;
use Modules\Shipping\Facades\ShippingMethod;
use Modules\Shipping\Method as ShippingMethodModel;

class AdminManualCartService
{
    private StorefrontCart $cart;

    public function __construct()
    {
        $this->cart = new StorefrontCart(
            new CartDatabaseStorage(),
            app('events'),
            'cart',
            'admin_manual_' . session()->getId(),
            config('fleetcart.modules.cart.config')
        );
    }

    public function cart(): StorefrontCart
    {
        return $this->cart;
    }

    public function getCartFromRequest(Request $request): void
    {
        $this->cart->clear();

        $items = $request->input('items', []);
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [];
        }

        collect($items)->each(function ($item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $variantId = $item['variant_id'] ?? null;
            $qty = (float) ($item['qty'] ?? 1);
            $options = $item['options'] ?? [];
            $manualUnitPrice = $item['manual_unit_price'] ?? null;

            if ($productId > 0 && $qty > 0) {
                $this->cart->store($productId, $variantId, $qty, $options);

                if ($manualUnitPrice !== null && is_numeric($manualUnitPrice)) {
                    $this->applyManualUnitPriceOverride($productId, $variantId, $options, (float) $manualUnitPrice);
                }
            }
        });
    }

    private function applyManualUnitPriceOverride(int $productId, $variantId, array $options, float $manualUnitPrice): void
    {
        $options = array_filter($options);
        $id = md5("product_id.{$productId}.variant_id.{$variantId}:options." . serialize($options));

        $content = $this->cart->getContent();

        if (! $content->has($id)) {
            return;
        }

        $item = $content->get($id);
        $item['price'] = $manualUnitPrice;
        $item['attributes']['manual_unit_price'] = $manualUnitPrice;
        $content->put($id, $item);
        $this->cart->save($content);
    }

    public function calculateTotals($customer, $shippingAddress = null, $billingAddress = null, $shippingMethodName = null, $shippingCostOverride = null): void
    {
        if (!$this->cart->allItemsAreVirtual()) {
            if ($shippingMethodName) {
                try {
                    $method = ShippingMethod::get($shippingMethodName);
                    if ($shippingCostOverride !== null && is_numeric($shippingCostOverride)) {
                        $this->cart->addShippingMethod(new ShippingMethodModel($method->name, $method->label, (float) $shippingCostOverride));
                    } else {
                        $this->cart->addShippingMethod($method);
                    }
                } catch (\Throwable $e) {
                    $available = ShippingMethod::available();
                    if ($available && $available->isNotEmpty()) {
                        $this->cart->addShippingMethod($available->first());
                    }
                }
            } else {
                $available = ShippingMethod::available();
                if ($available && $available->isNotEmpty()) {
                    $this->cart->addShippingMethod($available->first());
                }
            }
        }

        $billing = [
            'country' => ($billingAddress ? ($billingAddress->country ?? null) : ($shippingAddress?->country ?? null)),
            'state' => ($billingAddress ? ($billingAddress->state ?? null) : ($shippingAddress?->state ?? null)),
            'zip' => ($billingAddress ? ($billingAddress->zip ?? null) : ($shippingAddress?->zip ?? null)),
        ];

        $shipping = [
            'country' => $shippingAddress?->country ?? null,
            'state' => $shippingAddress?->state ?? null,
            'zip' => $shippingAddress?->zip ?? null,
        ];

        $this->cart->addTaxes((object) [
            'billing' => $billing,
            'shipping' => $shipping,
        ]);
    }
}

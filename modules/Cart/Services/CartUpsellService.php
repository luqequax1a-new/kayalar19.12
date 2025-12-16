<?php

namespace Modules\Cart\Services;

use Illuminate\Support\Collection;
use Modules\Cart\Cart;
use Modules\Cart\CartItem;
use Modules\Cart\Entities\CartUpsellRule;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Support\Money;

class CartUpsellService
{
    public function resolveBestRule(Cart $cart): ?array
    {
        $items = $cart->items();
        if ($items->isEmpty()) {
            return null;
        }

        $productIdsInCart = $items->map(fn($item) => optional($item->product)->id)->filter()->unique()->values();
        if ($productIdsInCart->isEmpty()) {
            return null;
        }

        \Log::info('[UPSSELL] resolveBestRule.cart', [
            'product_ids' => $productIdsInCart->all(),
            'subtotal' => $cart->subTotal()->amount(),
        ]);

        $rules = CartUpsellRule::query()
            ->active()
            ->forPlacement('checkout')
            ->withinDateRange()
            ->where(function ($q) use ($productIdsInCart) {
                $q->where('trigger_type', 'all_products')
                    ->orWhere(function ($q) use ($productIdsInCart) {
                        $q->where('trigger_type', 'product_to_product')
                            ->whereIn('main_product_id', $productIdsInCart);
                    });
            })
            ->orderByDesc('sort_order')
            ->orderBy('id')
            ->get();

        \Log::info('[UPSSELL] resolveBestRule.rules', [
            'count' => $rules->count(),
            'ids' => $rules->pluck('id')->all(),
        ]);

        $rule = $rules->first(function (CartUpsellRule $rule) use ($cart) {
            $ok = $this->cartMatchesRule($cart, $rule);

            \Log::info('[UPSSELL] resolveBestRule.evaluate', [
                'rule_id' => $rule->id,
                'ok' => $ok,
            ]);

            return $ok;
        });

        if (!$rule) {
            \Log::info('[UPSSELL] resolveBestRule.none_matched');
            return null;
        }

        $upsellProduct = $rule->upsellProduct()->first();
        if (!$upsellProduct) {
            return null;
        }

        $variant = null;
        if ($rule->preselected_variant_id) {
            $variant = ProductVariant::find($rule->preselected_variant_id);
        }

        $originalUnitPrice = $this->getOriginalPrice($upsellProduct, $variant);
        $upsellUnitPrice = $this->applyDiscount($originalUnitPrice, $rule->discount_type, (float) $rule->discount_value);

        return [
            'rule' => $rule,
            'product' => $upsellProduct,
            'variant' => $variant,
            'original_price' => $originalUnitPrice,
            'upsell_price' => $upsellUnitPrice,
        ];
    }


    /**
     * Resolve and validate a concrete upsell rule when adding an item.
     */
    public function resolveRuleForAdd(
        Cart $cart,
        int $ruleId,
        Product $product,
        ?ProductVariant $variant = null
    ): ?array {
        $rule = CartUpsellRule::active()
            ->forPlacement('checkout')
            ->withinDateRange()
            ->where('id', $ruleId)
            ->where('upsell_product_id', $product->id)
            ->first();

        if (!$rule) {
            return null;
        }

        if (!$this->cartMatchesRule($cart, $rule)) {
            return null;
        }

        $original = $this->getOriginalPrice($product, $variant);
        $upsell = $this->applyDiscount($original, $rule->discount_type, (float) $rule->discount_value);

        return [
            'rule' => $rule,
            'product' => $product,
            'variant' => $variant,
            'original_price' => $original,
            'upsell_price' => $upsell,
        ];
    }


    protected function cartMatchesRule(Cart $cart, CartUpsellRule $rule): bool
    {
        $items = $cart->items();
        if ($items->isEmpty()) {
            return false;
        }

        $productIdsInCart = $items->map(fn (CartItem $item) => optional($item->product)->id)
            ->filter()
            ->unique()
            ->values();

        $cartSubTotal = $cart->subTotal()->amount();

        if ($rule->trigger_type === 'product_to_product') {
            if (!$productIdsInCart->contains($rule->main_product_id)) {
                return false;
            }
        }

        if ($rule->min_cart_total !== null) {
            if ($cartSubTotal < (float) $rule->min_cart_total) {
                return false;
            }
        }

        if ($rule->max_cart_total !== null) {
            if ($cartSubTotal > (float) $rule->max_cart_total) {
                return false;
            }
        }

        if ($rule->hide_if_already_in_cart) {
            $upsellProductId = $rule->upsell_product_id;

            $hasUpsellInCart = $items->contains(function (CartItem $item) use ($upsellProductId) {
                return optional($item->product)->id === $upsellProductId;
            });

            if ($hasUpsellInCart) {
                return false;
            }
        }

        return true;
    }


    protected function getOriginalPrice(Product $product, ?ProductVariant $variant = null): float
    {
        $item = $variant ?: $product;

        if ($item->selling_price instanceof Money) {
            $amount = $item->selling_price->amount();

            return is_numeric($amount) ? (float) $amount : 0.0;
        }

        if ($item->selling_price === null) {
            if ($item->price instanceof Money) {
                $amount = $item->price->amount();

                return is_numeric($amount) ? (float) $amount : 0.0;
            }

            if ($item->price === null) {
                return 0.0;
            }

            return is_numeric($item->price) ? (float) $item->price : 0.0;
        }

        return is_numeric($item->selling_price) ? (float) $item->selling_price : 0.0;
    }

    protected function applyDiscount(float $original, string $type, float $value): float
    {
        if ($type === 'percent' && $value > 0) {
            return max($original * (1 - $value / 100), 0);
        }

        if ($type === 'fixed' && $value > 0) {
            return max($original - $value, 0);
        }

        return $original;
    }
}

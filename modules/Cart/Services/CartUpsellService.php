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
    public function resolveBestRule(Cart $cart, string $placement = 'checkout', ?Product $triggerProduct = null): ?array
    {
        $items = $cart->items();
        
        // Eager load categories to prevent N+1 queries - CartCollection doesn't support load()
        // Instead, manually load the relationships
        foreach ($items as $item) {
            if ($item->product && !$item->product->relationLoaded('categories')) {
                $item->product->load('categories');
            }
        }
        
        $productIdsInCart = $items->map(fn($item) => optional($item->product)->id)->filter()->unique()->values();
        $categoryIdsInCart = $items->flatMap(fn($item) => $item->product->categories->pluck('id'))->unique()->values();

        if ($triggerProduct) {
            // Ensure categories are loaded
            $triggerProduct->loadMissing('categories');
            
            $productIdsInCart->push($triggerProduct->id);
            $productIdsInCart = $productIdsInCart->unique()->values();
            
            $categoryIdsInCart = $categoryIdsInCart->merge($triggerProduct->categories->pluck('id'))->unique()->values();
        }

        $rules = CartUpsellRule::query()
            ->active()
            ->forPlacement($placement)
            ->withinDateRange()
            ->where(function ($q) use ($productIdsInCart, $categoryIdsInCart) {
                $q->where('trigger_type', 'all_products')
                    ->orWhere(function ($q) use ($productIdsInCart) {
                        $q->where('trigger_type', 'product_to_product')
                            ->whereIn('main_product_id', $productIdsInCart);
                    })
                    ->orWhere(function ($q) use ($categoryIdsInCart) {
                        $q->where('trigger_type', 'category_to_product')
                            ->whereIn('main_category_id', $categoryIdsInCart);
                    });
            })
            ->orderByDesc('sort_order')
            ->orderBy('id')
            ->get();

        $rule = $rules->first(function (CartUpsellRule $rule) use ($cart, $triggerProduct) {
            return $this->cartMatchesRule($cart, $rule, $triggerProduct);
        });

        if (!$rule) {
            return null;
        }

        $offers = $this->resolveOffersForRule($rule, $cart);

        if (empty($offers)) {
            return null;
        }

        return [
            'rule' => $rule,
            'offers' => $offers,
        ];
    }


    public function resolveOffersForRule(CartUpsellRule $rule, Cart $cart): array
    {
        $resolvedOffers = [];
        
        // 1. Include the legacy offer as the first one if it exists and matches criteria
        $legacyOffer = $this->resolveLegacyOffer($rule);
        if ($legacyOffer) {
            $showLegacy = true;
            if ($rule->hide_if_already_in_cart) {
                $legacyProduct = $legacyOffer['product'];
                $productInCart = $cart->items()->contains(function ($item) use ($legacyProduct) {
                    return optional($item->product)->id === (int) $legacyProduct->id;
                });
                
                if ($productInCart) {
                    $showLegacy = false;
                }
            }
            
            if ($showLegacy) {
                $resolvedOffers[] = $legacyOffer;
            }
        }

        // 2. Add dynamic offers
        $offerModels = $rule->offers()
            ->with(['product.files', 'product.variants.files', 'product.saleUnit', 'variant.files'])
            ->orderBy('order', 'asc')
            ->get();

        if ($offerModels->isNotEmpty()) {
            $firstOrder = (int) ($offerModels->min('order') ?? 0);

            foreach ($offerModels as $offerModel) {
                $product = $offerModel->product;
                
                if (!$product) {
                    continue;
                }

                // Check if this specific offer should be hidden when product is in cart
                // Use offer-level setting if available, otherwise fall back to rule-level setting
                $shouldCheckCart = $offerModel->hide_if_in_cart ?? $rule->hide_if_already_in_cart;
                
                if ($shouldCheckCart) {
                    // Only check dynamic offers if they are not specifically "always"
                    $isAlways = ((string) $offerModel->trigger) === 'always';

                    if (!$isAlways) {
                        $productInCart = $cart->items()->contains(function ($item) use ($product) {
                            return optional($item->product)->id === $product->id;
                        });

                        if ($productInCart) {
                            continue;
                        }
                    }
                }

                $hasVariants = $product->variants()->exists();
                $variant = null;

                if ($offerModel->variant_id) {
                    $variant = $offerModel->variant;
                } elseif ($hasVariants && !($offerModel->let_customer_choose ?? false)) {
                    $variant = $product->variants()->withoutGlobalScope('active')
                        ->orderByDesc('is_default')
                        ->first();
                }

                $letCustomerChoose = (bool) (($offerModel->let_customer_choose ?? false) || (!$offerModel->variant_id && $hasVariants));

                // Fallback for price calculation
                $priceVariant = $variant;
                if (!$priceVariant && $hasVariants) {
                     $priceVariant = $product->variants()->withoutGlobalScope('active')
                        ->orderByDesc('is_default')
                        ->first();
                }

                $originalUnitPrice = $this->getOriginalPrice($product, $priceVariant, $offerModel->discount_base_price);
                $upsellUnitPrice = $this->applyDiscount($originalUnitPrice, $offerModel->discount_type, (float) $offerModel->discount_value);

                $resolvedOffers[] = [
                    'offer_id' => $offerModel->id,
                    'order' => $offerModel->order,
                    'trigger' => $offerModel->trigger,
                    'product' => $product,
                    'variant' => $variant,
                    'original_price' => $originalUnitPrice,
                    'upsell_price' => $upsellUnitPrice,
                    'let_customer_choose' => $letCustomerChoose,
                    'title' => $offerModel->title,
                    'subtitle' => $offerModel->subtitle,
                ];
            }
        }

        return $resolvedOffers;
    }


    protected function resolveLegacyOffer(CartUpsellRule $rule): ?array
    {
        $upsellProduct = $rule->upsellProduct()
            ->with(['files', 'variants.files', 'saleUnit'])
            ->first();

        if (!$upsellProduct) {
            return null;
        }

        $hasVariants = $upsellProduct->variants()->exists();

        $variant = null;
        $letCustomerChoose = !$rule->preselected_variant_id && $hasVariants;

        if ($rule->preselected_variant_id) {
            $variant = ProductVariant::withoutGlobalScope('active')->find($rule->preselected_variant_id);
        } elseif ($hasVariants && !$letCustomerChoose) {
            // This case is technically impossible given the definition of $letCustomerChoose above,
            // but kept for symmetry with dynamic offers.
            $variant = $upsellProduct->variants()->withoutGlobalScope('active')
                ->orderByDesc('is_default')
                ->first();
        }

        // Fallback for price calculation (always need a variant/product for price)
        $priceVariant = $variant;
        if (!$priceVariant && $hasVariants) {
            $priceVariant = $upsellProduct->variants()->withoutGlobalScope('active')
                ->orderByDesc('is_default')
                ->first();
        }

        $originalUnitPrice = $this->getOriginalPrice($upsellProduct, $priceVariant, $rule->discount_base_price);
        $upsellUnitPrice = $this->applyDiscount($originalUnitPrice, $rule->discount_type, (float) $rule->discount_value);

        return [
            'offer_id' => null,
            'product' => $upsellProduct,
            'variant' => $variant,
            'original_price' => $originalUnitPrice,
            'upsell_price' => $upsellUnitPrice,
            'let_customer_choose' => $letCustomerChoose,
            'title' => $rule->title,
            'subtitle' => $rule->subtitle,
        ];
    }


    /**
     * Resolve and validate a concrete upsell rule when adding an item.
     */
    public function resolveRuleForAdd(
        Cart $cart,
        int $ruleId,
        Product $product,
        ?ProductVariant $variant = null,
        string $placement = 'checkout'
    ): ?array {
        $rule = CartUpsellRule::active()
            ->forPlacement($placement)
            ->withinDateRange()
            ->where('id', $ruleId)
            ->first();

        if (!$rule) {
            return null;
        }

        // Check if product exists in rule's offers or legacy upsell_product_id
        $offers = $rule->offers()->where('product_id', $product->id)->get();
        $isLegacy = $rule->upsell_product_id === $product->id;
        
        if ($offers->isEmpty() && !$isLegacy) {
            return null;
        }

        // For product page and post-checkout upsells, skip cart validation
        // Only validate cart contents for checkout page upsells
        if ($placement === 'checkout' || $placement === 'cart') {
            $cartMatches = $this->cartMatchesRule($cart, $rule);

            if (!$cartMatches) {
                return null;
            }
        }

        // Get discount from offer or rule
        $discountType = $rule->discount_type;
        $discountValue = (float) $rule->discount_value;
        $basePriceType = $rule->discount_base_price;

        if ($offers->isNotEmpty()) {
            $offer = $offers->first();
            $discountType = $offer->discount_type;
            $discountValue = (float) $offer->discount_value;
            $basePriceType = $offer->discount_base_price;
        }

        $original = $this->getOriginalPrice($product, $variant, $basePriceType);
        $upsell = $this->applyDiscount($original, $discountType, $discountValue);

        return [
            'rule' => $rule,
            'product' => $product,
            'variant' => $variant,
            'original_price' => $original,
            'upsell_price' => $upsell,
        ];
    }


    protected function cartMatchesRule(Cart $cart, CartUpsellRule $rule, ?Product $triggerProduct = null): bool
    {
        $items = $cart->items();
        
        // If we are not on a product page and cart is empty, no rules.
        if ($items->isEmpty() && !$triggerProduct) {
            return false;
        }

        $productIdsInCart = $items->map(fn (CartItem $item) => optional($item->product)->id)
            ->filter()
            ->unique()
            ->values();
            
        if ($triggerProduct) {
            $productIdsInCart->push($triggerProduct->id);
            $productIdsInCart = $productIdsInCart->unique()->values();
        }

        $cartSubTotal = $cart->subTotal()->amount();

        if ($rule->trigger_type === 'product_to_product') {
            if (!$productIdsInCart->contains($rule->main_product_id)) {
                return false;
            }
        }

        if ($rule->trigger_type === 'category_to_product' && $rule->main_category_id) {
            // Ensure categories are loaded to prevent N+1
            $items->load('product.categories');
            
            $categoryIdsMerged = $items->flatMap(fn($item) => $item->product->categories->pluck('id'));
            if ($triggerProduct) {
                $triggerProduct->loadMissing('categories');
                $categoryIdsMerged = $categoryIdsMerged->merge($triggerProduct->categories->pluck('id'));
            }
            
            if (!$categoryIdsMerged->contains($rule->main_category_id)) {
                return false;
            }
        }

        if ($rule->exclude_discounted_products) {
            $hasDiscounted = $items->contains(function (CartItem $item) {
                return $item->product->has_special_price || ($item->variant && $item->variant->has_special_price);
            });
            
            if (!$hasDiscounted && $triggerProduct) {
                $hasDiscounted = $triggerProduct->has_special_price;
            }

            if ($hasDiscounted) {
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

        // NOTE: Individual offer-level "hide if in cart" checks are done in resolveOffersForRule()
        // This global check was removed because it incorrectly hid ALL offers when ANY product was in cart.
        // Each offer now checks individually whether its specific product is in the cart.

        return true;
    }


    protected function getOriginalPrice(Product $product, ?ProductVariant $variant = null, ?string $baseType = 'special_price'): float
    {
        $price = 0;
        $useNormalPrice = $baseType === 'normal_price';

        if ($variant) {
            if ($useNormalPrice) {
                // If normal price is explicitly requested, ignore selling_price (special price)
                $price = (float) $variant->price->amount();
                if ($price <= 0) {
                    $price = (float) $variant->getRawOriginal('price');
                }
            } else {
                // Default: use selling_price if available, otherwise fallback to price
                $price = (float) ($variant->selling_price->amount() ?: $variant->price->amount());
                if ($price <= 0) {
                    $price = (float) $variant->getRawOriginal('selling_price') ?: (float) $variant->getRawOriginal('price');
                }
            }
        }

        if ($price <= 0) {
             if ($useNormalPrice) {
                $price = (float) $product->price->amount();
                if ($price <= 0) {
                    $price = (float) $product->getRawOriginal('price');
                }
             } else {
                $price = (float) ($product->selling_price->amount() ?: $product->price->amount());
                if ($price <= 0) {
                    $price = (float) $product->getRawOriginal('selling_price') ?: (float) $product->getRawOriginal('price');
                }
             }
        }

        // FIXED: Ensure price is always positive and valid
        if ($price <= 0) {
            \Log::warning('Invalid price detected for product/variant', [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'calculated_price' => $price,
                'base_type' => $baseType
            ]);
            $price = 0.01; // Minimum fallback price
        }

        return (float) $price;
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

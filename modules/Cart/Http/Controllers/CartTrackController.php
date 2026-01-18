<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Cart\Entities\Cart;
use Modules\Cart\Services\CartUpsellService;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Product\Services\ChosenProductOptions;
use Modules\Product\Services\ChosenProductVariations;
use Modules\Variation\Entities\VariationValue;

class CartTrackController extends Controller
{
    public function track($id)
    {
        $cart = Cart::where('id', $id)->first();

        if ($cart) {
            // Mark the abandoned cart as clicked
            $cart->update([
                'is_clicked' => true,
                'clicked_at' => now(),
                'superseded_at' => now(),
            ]);

            // Store the original abandoned cart ID in session to mark it as recovered later
            // This is the ONLY cart that should be marked as recovered when order is placed
            session()->put('recovered_from_cart_id', $id);

            // RESTORE CART LOGIC
            // Clear current cart first to avoid mixing old and new items
            try {
                \Modules\Cart\Facades\Cart::clear();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to clear cart before restoration: ' . $e->getMessage());
            }

            // Restore items from abandoned cart
            try {
                if (!empty($cart->data)) {
                    // The data is already unserialized by the Cart model's getDataAttribute
                    $items = $cart->data;
                    
                    // Handle both array and Collection types
                    if ($items instanceof \Illuminate\Support\Collection) {
                        $items = $items->all();
                    }
                    
                    // FIXED: Better validation for data format
                    if (!is_array($items) && !is_iterable($items)) {
                        throw new \Exception('Cart data is not in expected format: ' . gettype($items));
                    }
                    
                    if (empty($items)) {
                        \Illuminate\Support\Facades\Log::warning('Cart data is empty, nothing to restore', ['cart_id' => $id]);
                    }
                    
                    $restoredCount = 0;
                    $restoredUpsellCount = 0;
                    
                    foreach ($items as $itemKey => $item) {
                        try {
                            // FIXED: Better item validation
                            if (!is_object($item) && !is_array($item)) {
                                \Illuminate\Support\Facades\Log::warning('Invalid cart item format', [
                                    'item_key' => $itemKey,
                                    'item_type' => gettype($item)
                                ]);
                                continue;
                            }
                            
                            // Darryldecode cart stores items as ItemCollection objects
                            // Access properties directly instead of casting to array
                            if (is_object($item)) {
                                // ItemCollection object - access properties
                                $itemId = $item->id ?? $item->{'id'} ?? null;
                                $qty = $item->quantity ?? $item->{'quantity'} ?? 0;
                                $price = $item->price ?? $item->{'price'} ?? 0;
                                $name = $item->name ?? $item->{'name'} ?? '';
                                $attributes = $item->attributes ?? $item->{'attributes'} ?? [];
                                $conditions = $item->conditions ?? $item->{'conditions'} ?? [];
                            } else {
                                // Array format
                                $itemId = $item['id'] ?? null;
                                $qty = $item['quantity'] ?? 0;
                                $price = $item['price'] ?? 0;
                                $name = $item['name'] ?? '';
                                $attributes = $item['attributes'] ?? [];
                                $conditions = $item['conditions'] ?? [];
                            }
                            
                            // FIXED: Validate required fields
                            if (empty($itemId) || $qty <= 0) {
                                \Illuminate\Support\Facades\Log::warning('Skipping cart item with missing required fields', [
                                    'item_id' => $itemId,
                                    'qty' => $qty,
                                    'item_key' => $itemKey
                                ]);
                                continue;
                            }
                            
                            // Convert attributes to array if it's an object
                            if (is_object($attributes)) {
                                $attributes = json_decode(json_encode($attributes), true);
                            }

                            // FIXED: Validate attributes array
                            if (!is_array($attributes)) {
                                \Illuminate\Support\Facades\Log::warning('Invalid attributes format', [
                                    'item_id' => $itemId,
                                    'attributes_type' => gettype($attributes)
                                ]);
                                $attributes = [];
                            }

                            // Extract upsell state (email shows upsell items via this flag)
                            $isUpsell = false;
                            $upsellRuleId = null;
                            $upsellOriginalPrice = null;
                            $upsellUnitPrice = null;
                            if (is_array($attributes) && isset($attributes['upsell'])) {
                                $upsell = $attributes['upsell'];
                                if (is_object($upsell)) {
                                    $upsell = json_decode(json_encode($upsell), true);
                                }

                                if (is_array($upsell)) {
                                    $isUpsell = !empty($upsell['is_upsell']);
                                    $upsellRuleId = $upsell['rule_id'] ?? null;
                                    $upsellOriginalPrice = isset($upsell['original_price']) ? (float) $upsell['original_price'] : null;
                                    $upsellUnitPrice = isset($upsell['unit_price']) ? (float) $upsell['unit_price'] : null;
                                }
                            }

                            if ($itemId && $qty > 0 && is_array($attributes)) {
                                // Extract product and variant IDs from attributes
                                $productId = null;
                                $variantId = null;
                                $options = [];
                                
                                // Get product ID
                                if (isset($attributes['product'])) {
                                    if (is_object($attributes['product']) && isset($attributes['product']->id)) {
                                        $productId = $attributes['product']->id;
                                    } elseif (is_array($attributes['product']) && isset($attributes['product']['id'])) {
                                        $productId = $attributes['product']['id'];
                                    } elseif (is_numeric($attributes['product'])) {
                                        $productId = $attributes['product'];
                                    }
                                }
                                
                                // Get variant ID
                                if (isset($attributes['variant'])) {
                                    if (is_object($attributes['variant']) && isset($attributes['variant']->id)) {
                                        $variantId = $attributes['variant']->id;
                                    } elseif (is_array($attributes['variant']) && isset($attributes['variant']['id'])) {
                                        $variantId = $attributes['variant']['id'];
                                    } elseif (is_numeric($attributes['variant'])) {
                                        $variantId = $attributes['variant'];
                                    }
                                }
                                
                                // Get options
                                if (isset($attributes['options']) && (is_array($attributes['options']) || $attributes['options'] instanceof \Illuminate\Support\Collection)) {
                                    $rawOptions = $attributes['options'];
                                    if ($rawOptions instanceof \Illuminate\Support\Collection) {
                                        $rawOptions = $rawOptions->all();
                                    }

                                    foreach ($rawOptions as $option) {
                                        $optionId = null;
                                        $values = [];

                                        if (is_object($option)) {
                                            $optionId = $option->id ?? null;
                                            $values = $option->values ?? [];
                                        } elseif (is_array($option)) {
                                            $optionId = $option['id'] ?? null;
                                            $values = $option['values'] ?? [];
                                        }

                                        if (!$optionId) {
                                            continue;
                                        }

                                        if ($values instanceof \Illuminate\Support\Collection) {
                                            $values = $values->all();
                                        }

                                        $valueIds = [];
                                        if (is_array($values) || is_iterable($values)) {
                                            foreach ($values as $v) {
                                                if (is_object($v) && isset($v->id)) {
                                                    $valueIds[] = $v->id;
                                                } elseif (is_array($v) && isset($v['id'])) {
                                                    $valueIds[] = $v['id'];
                                                } elseif (is_numeric($v)) {
                                                    $valueIds[] = (int) $v;
                                                }
                                            }
                                        }

                                        $options[(int) $optionId] = $valueIds;
                                    }
                                }

                                if ($productId) {
                                    if ($isUpsell && $upsellRuleId) {
                                        $upsellRestored = false;
                                        $lastUpsellError = null;

                                        foreach (['checkout', 'cart'] as $placement) {
                                            try {
                                                $cartInstance = \Modules\Cart\Facades\Cart::instance();
                                                $cartInstance->storeUpsell([
                                                    'rule_id' => (int) $upsellRuleId,
                                                    'product_id' => (int) $productId,
                                                    'variant_id' => $variantId ? (int) $variantId : null,
                                                    'placement' => $placement,
                                                    'qty' => $qty,
                                                    'options' => $options,
                                                ]);

                                                $upsellRestored = true;
                                                $restoredUpsellCount++;

                                                break;
                                            } catch (\Exception $e) {
                                                $lastUpsellError = $e;
                                            }
                                        }

                                        if (!$upsellRestored) {
                                            // Fallback: restore with snapshot upsell metadata and unit price.
                                            // We do NOT want to fallback to normal Cart::store() because it loses the upsell badge/discount.
                                            \Illuminate\Support\Facades\Log::warning(
                                                'Failed to restore upsell item via rule validation, restoring with snapshot pricing: ' . ($lastUpsellError ? $lastUpsellError->getMessage() : 'unknown error'),
                                                [
                                                    'product_id' => $productId,
                                                    'variant_id' => $variantId,
                                                    'rule_id' => $upsellRuleId,
                                                ]
                                            );

                                            try {
                                                $product = Product::with('files', 'categories', 'taxClass')->findOrFail((int) $productId);
                                                $variant = $variantId ? ProductVariant::find($variantId) : null;

                                                $variations = [];
                                                if ($variant && !empty($variant->uids)) {
                                                    $uids = collect(explode('.', $variant->uids));
                                                    $rawVariations = $uids->map(function ($uid) {
                                                        return VariationValue::where('uid', $uid)->get()->pluck('id', 'variation.id');
                                                    });

                                                    foreach ($rawVariations as $variation) {
                                                        foreach ($variation as $variationId => $variationValueId) {
                                                            $variations[$variationId] = $variationValueId;
                                                        }
                                                    }
                                                }

                                                $chosenVariations = new ChosenProductVariations($product, $variations);
                                                $chosenOptions = new ChosenProductOptions($product, $options);

                                                $unitPriceToUse = (float) ($upsellUnitPrice && $upsellUnitPrice > 0 ? $upsellUnitPrice : $price);
                                                $originalPriceToUse = (float) ($upsellOriginalPrice && $upsellOriginalPrice > 0 ? $upsellOriginalPrice : 0);

                                                $cartInstance = \Modules\Cart\Facades\Cart::instance();
                                                $cartInstance->add([
                                                    'id' => md5('upsell:snapshot:rule.' . (int) $upsellRuleId . ":product_id." . (int) $productId . ".variant_id." . (int) ($variantId ?: 0) . ":options." . serialize($options)),
                                                    'name' => $product->name,
                                                    'price' => $unitPriceToUse,
                                                    'quantity' => (float) $qty,
                                                    'attributes' => [
                                                        'product' => $product,
                                                        'variant' => $variant,
                                                        'item' => $variant ?: $product,
                                                        'variations' => $chosenVariations->getEntities(),
                                                        'options' => $chosenOptions->getEntities(),
                                                        'created_at' => time(),
                                                        'upsell' => [
                                                            'is_upsell' => true,
                                                            'rule_id' => (int) $upsellRuleId,
                                                            'original_price' => $originalPriceToUse,
                                                            'unit_price' => $unitPriceToUse,
                                                        ],
                                                    ],
                                                ]);

                                                $restoredUpsellCount++;
                                            } catch (\Exception $e) {
                                                // Last resort fallback.
                                                \Illuminate\Support\Facades\Log::error('Failed to restore upsell snapshot item, falling back to normal item: ' . $e->getMessage(), [
                                                    'product_id' => $productId,
                                                    'variant_id' => $variantId,
                                                    'rule_id' => $upsellRuleId,
                                                ]);

                                                \Modules\Cart\Facades\Cart::store(
                                                    $productId,
                                                    $variantId,
                                                    $qty,
                                                    $options
                                                );
                                                $restoredCount++;
                                            }
                                        }
                                    } else {
                                        // Use Cart::store() to properly recreate the item with fresh models
                                        \Modules\Cart\Facades\Cart::store(
                                            $productId,
                                            $variantId,
                                            $qty,
                                            $options
                                        );
                                        
                                        $restoredCount++;
                                    }
                                } else {
                                    \Illuminate\Support\Facades\Log::warning('Could not extract product ID from cart item', [
                                        'itemId' => $itemId,
                                        'attributes' => $attributes,
                                    ]);
                                }
                            } else {
                                \Illuminate\Support\Facades\Log::warning('Skipped invalid cart item', [
                                    'itemId' => $itemId,
                                    'qty' => $qty,
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to restore individual cart item', [
                                'error' => $e->getMessage(),
                                'item_key' => $itemKey,
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }

                    // RESTORE CONDITIONS (COUPONS)
                    $conditionsId = str_replace('_cart_items', '_cart_conditions', $id);
                    $conditionsCart = Cart::where('id', $conditionsId)->first();
                    
                    if ($conditionsCart && !empty($conditionsCart->data)) {
                        $conditions = $conditionsCart->data;
                        
                        // Handle both array and Collection types
                        if ($conditions instanceof \Illuminate\Support\Collection) {
                            $conditions = $conditions->all();
                        }
                        
                        // Restore cart conditions (coupons, discounts, etc.)
                        if (is_array($conditions) || is_iterable($conditions)) {
                            foreach ($conditions as $condition) {
                                try {
                                    \Modules\Cart\Facades\Cart::condition($condition);
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::warning('Failed to restore cart condition: ' . $e->getMessage());
                                }
                            }
                        }
                    }
                    
                    
                    // RECALCULATE UPSELL OFFERS FOR RESTORED CART
                    // This ensures cart offers (upsell/cross-sell) work with abandoned carts
                    try {
                        $restoredCart = \Modules\Cart\Facades\Cart::instance();
                        $upsellService = app(CartUpsellService::class);
                        $upsellOffer = $upsellService->resolveBestRule($restoredCart, 'checkout');
                        
                        if ($upsellOffer) {
                            session()->put('cart_upsell_offer', $upsellOffer);
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning('Failed to recalculate upsell offer: ' . $e->getMessage());
                    }
                }

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to restore abandoned cart items: ' . $e->getMessage(), [
                    'cart_id' => $id,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $redirectTo = request('redirect_to');
        
        if ($redirectTo && filter_var($redirectTo, FILTER_VALIDATE_URL)) {
             return redirect()->to($redirectTo);
        }

        return redirect()->route('cart.index');
    }
}

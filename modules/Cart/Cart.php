<?php

namespace Modules\Cart;

use JsonSerializable;
use Modules\Support\Money;
use Modules\Tax\Entities\TaxRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Coupon\Entities\Coupon;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Shipping\Facades\ShippingMethod;
use Darryldecode\Cart\Cart as DarryldecodeCart;
use Modules\Variation\Entities\VariationValue;
use Modules\Product\Services\ChosenProductOptions;
use Modules\Product\Services\ChosenProductVariations;
use Darryldecode\Cart\Exceptions\InvalidItemException;
use Darryldecode\Cart\Exceptions\InvalidConditionException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Modules\Cart\Services\CartUpsellService;

class Cart extends DarryldecodeCart implements JsonSerializable
{
    /**
     * Get the current instance.
     *
     * @return $this
     */
    public function instance(): static
    {
        return $this;
    }


    /**
     * Clear the cart.
     *
     * @return void
     */
    public function clear(): void
    {
        try {
            \Log::info('[CART] clear.before', [
                'session_id' => session()->getId(),
                'count' => $this->count(),
            ]);
        } catch (\Throwable $e) {
        }

        parent::clear();

        $this->clearCartConditions();

        try {
            \Log::info('[CART] clear.after', [
                'session_id' => session()->getId(),
                'count' => $this->count(),
            ]);
        } catch (\Throwable $e) {
        }
    }


    /**
     * Store an upsell item in the cart with a direct line discount.
     *
     * This does NOT use coupons; it simply overrides the unit price for
     * this line and stores debug metadata under the "upsell" attribute.
     *
     * @param array $data
     *
     * @throws InvalidItemException
     */
    public function storeUpsell(array $data): void
    {
        $productId = (int) ($data['product_id'] ?? 0);
        $variantId = $data['variant_id'] ?? null;
        $qty = $data['qty'] ?? 1;
        $qty = (float) (is_numeric($qty) ? $qty : 1);

        if ($productId <= 0 || $qty <= 0) {
            throw new InvalidItemException('Invalid upsell payload.');
        }

        $options = $data['options'] ?? [];
        $options = is_array($options) ? $options : [];

        $product = Product::with('files', 'categories', 'taxClass', 'variants')->findOrFail($productId);
        $variant = null;
        if (!empty($variantId)) {
            $variant = $product->variants()->where('id', $variantId)->firstOrFail();
        }

        /** @var CartUpsellService $upsellService */
        $upsellService = app(CartUpsellService::class);

        $resolved = $upsellService->resolveRuleForAdd(
            $this,
            (int) ($data['rule_id'] ?? 0),
            $product,
            $variant,
        );

        if (!$resolved) {
            throw new InvalidItemException('Upsell rule is no longer valid.');
        }

        $unitPrice = (float) $resolved['upsell_price'];
        $originalPrice = (float) $resolved['original_price'];
        $ruleId = $resolved['rule']->id;

        $variations = [];
        if ($variant) {
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

        $this->add([
            'id' => md5('upsell:rule.' . $ruleId . ":product_id.{$productId}.variant_id.{$variantId}:options." . serialize($options)),
            'name' => $product->name,
            'price' => $unitPrice,
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
                    'rule_id' => $ruleId,
                    'original_price' => $originalPrice,
                    'unit_price' => $unitPrice,
                ],
            ],
        ]);

        try {
            \Log::info('[CART] storeUpsell.after', [
                'session_id' => session()->getId(),
                'count' => $this->count(),
                'rule_id' => $ruleId,
            ]);
        } catch (\Throwable $e) {
        }
    }


    /**
     * Store a new item to the cart.
     *
     * @param int $productId
     * @param       $variantId
     * @param int $qty
     * @param array $options
     *
     * @return void
     * @throws InvalidItemException
     */
    public function store($productId, $variantId, $qty, $options = []): void
    {
        $options = array_filter($options);
        $variations = [];

        try {
            \Log::info('[CART] store.before', [
                'session_id' => session()->getId(),
                'count' => $this->count(),
                'product_id' => $productId,
                'variant_id' => $variantId,
                'qty' => $qty,
            ]);
        } catch (\Throwable $e) {
        }

        $product = Product::with('files', 'categories', 'taxClass')->findOrFail($productId);
        $variant = ProductVariant::find($variantId);
        $item = $variant ?? $product;

        if ($variant) {
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

        $this->add([
            'id' => md5("product_id.{$productId}.variant_id.{$variantId}:options." . serialize($options)),
            'name' => $product->name,
            'price' => $item->selling_price->amount(),
            'quantity' => (float)$qty,
            'attributes' => [
                'product' => $product,
                'variant' => $variant,
                'item' => $item,
                'variations' => $chosenVariations->getEntities(),
                'options' => $chosenOptions->getEntities(),
                'created_at' => time(),
            ],
        ]);

        try {
            \Log::info('[CART] store.after', [
                'session_id' => session()->getId(),
                'count' => $this->count(),
            ]);
        } catch (\Throwable $e) {
        }
    }


    public function updateQuantity($id, $qty)
    {
        $cart = $this->getContent();

        if ($cart->has($id)) {
            $item = $cart->get($id);
            $item['quantity'] = (float) $qty;
            $cart->put($id, $item);
            $this->save($cart);
        }
    }


    /**
     * Total quantity of the given cartItem
     * in the Cart.
     *
     * @param CartItem $cartItem
     *
     * @return int
     */
    public function addedQty(CartItem $cartItem): float
    {
        $items = $this->items()->filter(function ($cartItemAlias) use ($cartItem) {
            if ($cartItem->variant && $cartItemAlias->variant) {
                return $cartItemAlias->variant->id === $cartItem->variant->id;
            }

            return $cartItemAlias->product->id === $cartItem->product->id;
        });

        return (float) $items->sum('qty');
    }


    public function items()
    {
        return $this->getContent()
            ->sortBy('attributes.created_at', SORT_REGULAR, true)
            ->map(function ($item) {
                return new CartItem($item);
            });
    }


    public function crossSellProducts()
    {
        return $this->getAllProducts()
            ->load([
                'crossSellProducts' => function ($query) {
                    $query->forCard();
                },
            ])
            ->pluck('crossSellProducts')
            ->flatten();
    }


    public function getAllProducts(): EloquentCollection
    {
        return $this->items()->map(function ($cartItem) {
            return $cartItem->product;
        })->flatten()->pipe(function ($products) {
            return new EloquentCollection($products);
        });
    }


    public function reduceStock()
    {
        $this->manageStock(function ($cartItem) {
            $cartItem->item->decrement('qty', $cartItem->qty);
        });
    }


    public function restoreStock()
    {
        $this->manageStock(function ($cartItem) {
            $cartItem->product->increment('qty', $cartItem->qty);
        });
    }


    /**
     * @throws InvalidConditionException
     */
    public function addShippingMethod($shippingMethod)
    {
        $this->removeShippingMethod();

        $this->condition(
            new CartCondition([
                'name' => $shippingMethod->label,
                'type' => 'shipping_method',
                'target' => 'total',
                'value' => $this->coupon()?->free_shipping ? 0 : $shippingMethod->cost->amount(),
                'order' => 1,
                'attributes' => [
                    'shipping_method' => $shippingMethod,
                ],
            ]),
        );

        return $this->shippingMethod();
    }


    /**
     * @throws InvalidConditionException
     */
    public function addCodFee($fee)
    {
        $this->removeCodFee();

        $amount = $fee instanceof Money ? $fee->amount() : $fee;

        if ($amount <= 0) {
            return $this->codFee();
        }

        $this->condition(
            new CartCondition([
                'name' => 'cod_fee',
                'type' => 'cod_fee',
                'target' => 'total',
                'value' => $amount,
                'order' => 2,
                'attributes' => [],
            ]),
        );

        return $this->codFee();
    }


    public function removeShippingMethod()
    {
        $this->removeConditionsByType('shipping_method');
    }


    public function removeCodFee()
    {
        $this->removeConditionsByType('cod_fee');
    }


    public function coupon()
    {
        if (!$this->hasCoupon()) {
            return new NullCartCoupon();
        }

        $couponCondition = $this->getConditionsByType('coupon')->first();
        $coupon = Coupon::with('products', 'categories')->find($couponCondition->getAttribute('coupon_id'));

        return new CartCoupon($this, $coupon, $couponCondition);
    }


    public function isEmpty()
    {
        return $this->items()->isEmpty();
    }


    public function hasCoupon()
    {
        if ($this->getConditionsByType('coupon')->isEmpty()) {
            return false;
        }

        $couponId = $this->getConditionsByType('coupon')
            ->first()
            ->getAttribute('coupon_id');

        return Coupon::where('id', $couponId)->exists();
    }


    /**
     * @throws InvalidConditionException
     */
    public function applyCoupon(Coupon $coupon)
    {
        $this->removeCoupon();

        try {
            $this->condition(
                new CartCondition([
                    'name' => $coupon->code,
                    'type' => 'coupon',
                    'target' => 'total',
                    'value' => $this->getCouponValue($coupon),
                    'order' => 2,
                    'attributes' => [
                        'coupon_id' => $coupon->id,
                    ],
                ]),
            );
        } catch (InvalidConditionException $e) {
            if (app()->hasDebugModeEnabled()) {
                $message = $e->getMessage();
            } else {
                $message = trans('core::something_went_wrong');
            }

            if (request()->ajax()) {
                return response()->json(
                    [
                        'message' => $message,
                    ],
                    400
                );
            }
        }

        if ($coupon->free_shipping) {
            $this->addShippingMethod(
                ShippingMethod::get(
                    $this->shippingMethod()->name()
                )
            );
        }
    }


    public function removeCoupon()
    {
        $this->removeConditionsByType('coupon');
    }


    public function shippingMethod()
    {
        if (!$this->hasShippingMethod()) {
            return new NullCartShippingMethod();
        }

        return new CartShippingMethod($this, $this->getConditionsByType('shipping_method')->first());
    }


    public function hasShippingMethod()
    {
        return $this->getConditionsByType('shipping_method')->isNotEmpty();
    }


    public function couponAlreadyApplied(Coupon $coupon)
    {
        return $this->coupon()->code() === $coupon->code;
    }


    public function discount()
    {
        return $this->coupon()->value();
    }


    public function addTaxes($addTaxesToCartRequest)
    {
        // Store prices are VAT-inclusive: do not add taxes on top of total.
        // Keep cart totals consistent for both guest and logged-in customers.
        if (setting('prices_include_tax')) {
            $this->removeTaxes();

            return;
        }

        $this->removeTaxes();

        $this->findTaxes(
            $addTaxesToCartRequest->billing,
            $addTaxesToCartRequest->shipping
        )->each(
            /**
             * @throws InvalidConditionException
             */
            function ($taxRate) {
                $this->condition(
                    new CartCondition([
                        'name' => $taxRate->id,
                        'type' => 'tax',
                        'target' => 'total',
                        'value' => "+{$taxRate->rate}%",
                        'order' => 3,
                        'attributes' => [
                            'tax_rate_id' => $taxRate->id,
                        ],
                    ]),
                );
            }
        );
    }


    public function removeTaxes()
    {
        $this->removeConditionsByType('tax');
    }


    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }


    public function jsonSerialize(): array
    {
        return $this->toArray();
    }


    public function count(): int
    {
        return $this->items()->count();
    }


    public function toArray(): array
    {
        return [
            'items' => $this->items(),
            'quantity' => $this->getTotalQuantity(),
            'availableShippingMethods' => $this->availableShippingMethods(),
            'subTotal' => $this->subTotal(),
            'shippingMethodName' => $this->shippingMethod()->name(),
            'shippingCost' => $this->shippingCost(),
            'codFee' => $this->codFee(),
            'coupon' => $this->coupon(),
            'taxes' => $this->taxes(),
            'total' => $this->total(),
        ];
    }


    public function availableShippingMethods(): Collection
    {
        if ($this->allItemsAreVirtual()) {
            return collect();
        }

        return ShippingMethod::available();
    }


    public function allItemsAreVirtual()
    {
        return $this->items()->every(function (CartItem $cartItem) {
            return $cartItem->product->is_virtual;
        });
    }


    public function subTotal()
    {
        return Money::inDefaultCurrency($this->getSubTotal())->add($this->optionsPrice());
    }


    public function shippingCost()
    {
        $base = $this->shippingMethod()->cost();

        // Eğer COD ücreti "kargoya ekle" modunda ise, kargo tutarına COD
        // ücretini dahil et.
        if (setting('cod_fee_display_mode') === 'add_to_shipping') {
            return $base->add($this->codFee());
        }

        return $base;
    }


    public function taxes()
    {
        if (!$this->hasTax()) {
            return new Collection();
        }

        $taxConditions = $this->getConditionsByType('tax');
        $ids = $this->getTaxRateIds($taxConditions)->values()->all();
        sort($ids);
        $cacheKey = 'tax_rates:'.implode('-', $ids);
        $taxRates = Cache::remember($cacheKey, 600, function () use ($ids) {
            return TaxRate::whereIn('id', $ids)->get();
        });

        return $taxConditions->map(function ($taxCondition) use ($taxRates) {
            $taxRate = $taxRates->where('id', $taxCondition->getAttribute('tax_rate_id'))->first();

            return new CartTax($this, $taxRate, $taxCondition);
        });
    }


    public function hasTax()
    {
        return $this->getConditionsByType('tax')->isNotEmpty();
    }


    public function codFee()
    {
        $condition = $this->getConditionsByType('cod_fee')->first();

        if (!$condition) {
            return Money::inDefaultCurrency(0);
        }

        $value = (float) $condition->getValue();

        if ($value <= 0) {
            return Money::inDefaultCurrency(0);
        }

        return Money::inDefaultCurrency($value);
    }


    public function total()
    {
        $subTotal = $this->subTotal();

        // Some pages (cart / first load) may not have a shipping method selected yet.
        // Be defensive and fallback to zero shipping cost in that case.
        $shippingMethod = $this->hasShippingMethod() ? $this->shippingMethod() : null;

        $shippingCost = $shippingMethod
            ? $this->shippingCost()
            : Money::inDefaultCurrency(0);

        // Eğer COD ücreti kargo satırına ekleniyorsa (add_to_shipping), toplam
        // hesaplanırken ayrıca eklenmemelidir.
        $codFee = setting('cod_fee_display_mode') === 'add_to_shipping'
            ? Money::inDefaultCurrency(0)
            : $this->codFee();

        return $subTotal
            ->add($shippingCost)
            ->add($codFee)
            ->subtract($this->coupon()->value())
            ->add($this->tax());
    }


    public function tax()
    {
        return Money::inDefaultCurrency($this->calculateTax());
    }


    private function manageStock($callback)
    {
        $this->items()
            ->filter(function ($cartItem) {
                return $cartItem->item->manage_stock;
            })
            ->each($callback);
    }


    private function refreshFreeShippingCoupon()
    {
        if ($this->coupon()->isFreeShipping()) {
            $this->applyCoupon($this->coupon()->entity());
        }
    }


    private function getCouponValue($coupon)
    {
        if ($coupon->is_percent) {
            return "-{$coupon->value}%";
        }

        return "-{$coupon->value->amount()}";
    }


    private function findTaxes($billing_address, $shipping_address)
    {
        return $this->items()
            ->groupBy('tax_class_id')
            ->flatten()
            ->map(function (CartItem $cartItem) use ($billing_address, $shipping_address) {
                return $cartItem->findTax($billing_address, $shipping_address);
            })
            ->filter();
    }


    private function optionsPrice()
    {
        return Money::inDefaultCurrency($this->calculateOptionsPrice());
    }


    private function calculateOptionsPrice()
    {
        return $this->items()->sum(function ($cartItem) {
            return $cartItem
                ->optionsPrice()
                ->multiply($cartItem->qty)
                ->amount();
        });
    }


    private function getTaxRateIds($taxConditions)
    {
        return $taxConditions->map(function ($taxCondition) {
            return $taxCondition->getAttribute('tax_rate_id');
        });
    }


    private function calculateTax()
    {
        return $this->taxes()->sum(function ($cartTax) {
            return $cartTax->amount()->amount();
        });
    }
}

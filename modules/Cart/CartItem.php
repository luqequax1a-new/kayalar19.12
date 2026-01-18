<?php

namespace Modules\Cart;

use stdClass;
use JsonSerializable;
use Modules\Support\Money;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

class CartItem implements JsonSerializable
{
    private static array $productStockCache = [];
    private static array $variantStockCache = [];


    public static function setProductStockCache(int $id, $product): void
    {
        self::$productStockCache[$id] = $product;
    }


    public static function setVariantStockCache(int $id, $variant): void
    {
        self::$variantStockCache[$id] = $variant;
    }

    /**
     * Clear stock cache for specific product or variant
     */
    public static function clearProductStockCache(int $id): void
    {
        unset(self::$productStockCache[$id]);
    }

    public static function clearVariantStockCache(int $id): void
    {
        unset(self::$variantStockCache[$id]);
    }

    /**
     * Clear all stock caches
     */
    public static function clearAllStockCache(): void
    {
        self::$productStockCache = [];
        self::$variantStockCache = [];
    }

    /**
     * The ID of the cart item.
     *
     * @var int
     */
    public $id;

    /**
     * Quantity of the cart item.
     *
     * @var float
     */
    public $qty;

    /**
     * Underlying product of the cart item.
     *
     * @var Product
     */
    public $product;

    /**
     * Underlying product variant of the cart item.
     *
     * @var array
     */
    public $variant;

    /**
     * The registered custom driver creators.
     *
     * @var
     */
    public $item;

    /**
     * Options of the cart item.
     *
     * @var array
     */
    public $options;

    /**
     * Variations of the cart item.
     *
     * @var array
     */
    public $variations;


    /**
     * Optional upsell metadata for the cart item.
     *
     * @var array|null
     */
    public $upsell;
    public $manual_unit_price;


    /**
     * @param $item
     */
    public function __construct($item)
    {
        $this->id = $item->id;
        $this->qty = (float) ($item->quantity ?? 0);
        $this->product = $item->attributes['product'] ?? null;
        $this->variant = $item->attributes['variant'] ?? null;
        $this->item = $item->attributes['item'] ?? null;
        $this->variations = $item->attributes['variations'] ?? collect([]);
        $this->options = $item->attributes['options'] ?? collect([]);
        $this->upsell = $item->attributes['upsell'] ?? null;
        $this->manual_unit_price = $item->attributes['manual_unit_price'] ?? null;
    }


    /**
     * @return $this
     */
    public function refreshStock()
    {
        $item = $this->getItem();

        if (!$item) {
            return $this;
        }

        $this->item->fill([
            'manage_stock' => $item->manage_stock,
            'in_stock' => $item->in_stock,
            'qty' => $item->qty,
        ]);

        return $this;
    }


    /**
     * @return mixed
     */
    private function getProduct()
    {
        $id = (int) ($this->product->id ?? 0);

        if ($id <= 0) {
            return null;
        }

        if (!array_key_exists($id, self::$productStockCache)) {
            self::$productStockCache[$id] = Product::withName()
                ->addSelect('id', 'in_stock', 'manage_stock', 'qty', 'is_active')
                ->where('id', $id)
                ->first();
        }

        return self::$productStockCache[$id];
    }


    /**
     * @return mixed
     */
    private function getVariant()
    {
        $id = (int) ($this->variant->id ?? 0);

        if ($id <= 0) {
            return null;
        }

        if (!array_key_exists($id, self::$variantStockCache)) {
            self::$variantStockCache[$id] = ProductVariant::query()
                ->without(['files'])
                ->addSelect('id', 'in_stock', 'manage_stock', 'qty', 'is_active')
                ->where('id', $id)
                ->first();
        }

        return self::$variantStockCache[$id];
    }


    /**
     * @return mixed
     */
    public function getItem()
    {
        if ($this->item instanceof ProductVariant) {
            return $this->getVariant();
        }

        return $this->getProduct();
    }


    /**
     * @param array $billing_address
     * @param array $shipping_address
     *
     * @return mixed
     */
    public function findTax($billing_address, $shipping_address)
    {
        return $this->product->taxClass
            ->findTaxRate($billing_address, $shipping_address);
    }


    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }


    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'qty' => $this->qty,
            'product' => $this->product->clean(),
            'variant' => $this->variant?->clean(),
            'item' => $this->refreshStock()->item,
            'variations' => $this->variations->isNotEmpty() ? $this->variations->keyBy('position') : new stdClass(),
            'options' => $this->options->isNotEmpty() ? $this->options->keyBy('position') : new stdClass(),
            'unitPrice' => $this->unitPrice(),
            'total' => $this->totalPrice(),
            'upsell' => $this->upsell ?: new stdClass(),
        ];
    }


    /**
     * Calculate the unit price of the cart item.
     *
     * @return mixed
     */
    public function unitPrice()
    {
        if ($this->manual_unit_price !== null && is_numeric($this->manual_unit_price)) {
            return Money::inDefaultCurrency((float) $this->manual_unit_price)
                ->add($this->optionsPrice());
        }

        if (is_array($this->upsell) && isset($this->upsell['unit_price'])) {
            return Money::inDefaultCurrency((float) $this->upsell['unit_price'])
                ->add($this->optionsPrice());
        }

        return $this->item->selling_price->add($this->optionsPrice());
    }


    /**
     * Calculate the price of the options
     * of the cart item.
     *
     * @return \Modules\Support\Money
     */
    public function optionsPrice()
    {
        return Money::inDefaultCurrency($this->calculateOptionsPrice());
    }


    /**
     * Calculate the total price of the cart item.
     *
     * @return mixed
     */
    public function totalPrice()
    {
        return $this->unitPrice()->multiply($this->qty);
    }


    /**
     * Calculate the price of the options.
     *
     * @return float
     */
    private function calculateOptionsPrice()
    {
        return (float)$this->options
            ->sum(
                fn ($option) => $this->sumOfThePricesOfTheValuesOf($option)
            );
    }


    /**
     * Calculate the sum of the prices of the
     * values of the given option.
     *
     * @param $option
     *
     * @return float
     */
    private function sumOfThePricesOfTheValuesOf($option)
    {
        return (float)$option->values
            ->sum(function ($value) {
                return $value->price_type === 'fixed'
                    ? $value->price->amount()
                    : take_percent(
                        $value->price,
                        $this->item->selling_price->amount()
                    );
            });
    }
}

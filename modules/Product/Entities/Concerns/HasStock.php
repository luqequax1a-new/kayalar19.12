<?php

namespace Modules\Product\Entities\Concerns;

use Modules\FlashSale\Entities\FlashSale;

trait HasStock
{
    public function isOutOfStock(): bool
    {
        return !$this->isInStock();
    }


    public function isInStock()
    {
        if (FlashSale::contains($this)) {
            return FlashSale::remainingQty($this) > 0;
        }
        if ($this->hasAnyVariants()) {
            $variantWithStock = $this->variants
                ->where('in_stock', true)
                ->first(function ($variant) {
                    try {
                        if (! (bool) $variant->manage_stock) {
                            return true;
                        }

                        return (float) ($variant->qty ?? 0) > 0;
                    } catch (\Throwable $e) {
                        return false;
                    }
                });

            return (bool) $variantWithStock;
        } else {
            if ($this->manage_stock && (float) $this->qty <= 0) {
                return false;
            }

            return (bool) $this->in_stock;
        }
    }


    public function markAsInStock(): void
    {
        $this->withoutEvents(function () {
            $this->update(['in_stock' => true]);
        });

        try {
            app(\Modules\Product\Listeners\SendBackInStockNotifications::class)->handle($this);
        } catch (\Throwable $e) {
            report($e);
        }
    }


    public function markAsOutOfStock(): void
    {
        $this->withoutEvents(function () {
            $this->update(['in_stock' => false]);
        });
    }
}

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
            return $this->variants->contains(function ($variant) {
                try {
                    return $variant->isInStock();
                } catch (\Throwable $e) {
                    return false;
                }
            });
        }

        if ($this->manage_stock) {
            return (float) ($this->qty ?? 0) > 0;
        }

        return true;
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

<?php

namespace Modules\Product\Listeners;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Modules\Product\Mail\BackInStockMail;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Product\Entities\StockNotifyRequest;

class SendBackInStockNotifications
{
    public function handle($model): void
    {
        Log::info('[STOCK_NOTIFY] listener.handle', [
            'model' => is_object($model) ? get_class($model) : gettype($model),
            'id' => is_object($model) && isset($model->id) ? $model->id : null,
        ]);

        if ($model instanceof ProductVariant) {
            $this->handleVariant($model);

            return;
        }

        $product = $this->resolveProduct($model);

        if (! $product) {
            return;
        }

        $pending = StockNotifyRequest::query()
            ->where('product_id', $product->id)
            ->whereNull('variant_id')
            ->whereNull('sent_at')
            ->get();

        if ($pending->isEmpty()) {
            Log::info('[STOCK_NOTIFY] no pending product requests', [
                'product_id' => $product->id,
            ]);
            return;
        }

        $product->setRelation('variants', $product->variants()->withoutGlobalScope('active')->get());

        if (! $product->isInStock()) {
            Log::info('[STOCK_NOTIFY] product not in stock; skipping', [
                'product_id' => $product->id,
                'manage_stock' => $product->manage_stock,
                'qty' => $product->qty,
                'in_stock' => $product->in_stock,
            ]);
            return;
        }

        foreach ($pending->groupBy('email') as $email => $requests) {
            try {
                Log::info('[STOCK_NOTIFY] sending mail (product)', [
                    'product_id' => $product->id,
                    'email' => $email,
                    'request_ids' => $requests->pluck('id')->all(),
                ]);

                Mail::to($email)->send(new BackInStockMail($product));

                StockNotifyRequest::query()
                    ->whereIn('id', $requests->pluck('id')->all())
                    ->update(['sent_at' => now()]);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    private function handleVariant(ProductVariant $variant): void
    {
        if (! $variant->isInStock()) {
            Log::info('[STOCK_NOTIFY] variant not in stock; skipping', [
                'variant_id' => $variant->id,
                'manage_stock' => $variant->manage_stock,
                'qty' => $variant->qty,
                'in_stock' => $variant->in_stock,
            ]);
            return;
        }

        $product = $variant->product()->withoutGlobalScope('active')->first();

        if (! $product) {
            return;
        }

        $product->setRelation('variants', $product->variants()->withoutGlobalScope('active')->get());

        if (! $product->isInStock()) {
            return;
        }

        $pending = StockNotifyRequest::query()
            ->whereNull('sent_at')
            ->where(function ($q) use ($variant, $product) {
                $q->where('variant_id', $variant->id)
                    ->orWhere(function ($q) use ($product) {
                        $q->where('product_id', $product->id)
                            ->whereNull('variant_id');
                    });
            })
            ->get();

        foreach ($pending->groupBy('email') as $email => $requests) {
            try {
                Log::info('[STOCK_NOTIFY] sending mail (variant)', [
                    'variant_id' => $variant->id,
                    'product_id' => $product->id,
                    'email' => $email,
                    'request_ids' => $requests->pluck('id')->all(),
                ]);

                Mail::to($email)->send(new BackInStockMail($product, $variant));

                StockNotifyRequest::query()
                    ->whereIn('id', $requests->pluck('id')->all())
                    ->update(['sent_at' => now()]);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    private function resolveProduct($model): ?Product
    {
        if ($model instanceof Product) {
            return $model;
        }

        if ($model instanceof ProductVariant) {
            return $model->product()->withoutGlobalScope('active')->first();
        }

        return null;
    }
}

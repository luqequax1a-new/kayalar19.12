<?php

namespace Modules\Product\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Product\Entities\Product;

class AdminStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public Product $product;
    public $variantName;
    public $qty;
    public $isOutOfStock;
    public $sku;
    public $imageUrl;
    public $variationLabels;

    public function __construct(Product $product, $qty, $variantName = null, $imageUrl = null, $sku = null, $variationLabels = [])
    {
        $this->product = $product;
        $this->qty = $qty;
        $this->variantName = $variantName;
        $this->isOutOfStock = ((float)$qty <= 0);
        $this->imageUrl = $imageUrl ?: $this->getDefaultProductImage($product);
        $this->sku = $sku ?: $product->sku;
        $this->variationLabels = $variationLabels;
    }

    private function getDefaultProductImage($product)
    {
        try {
            if ($product->base_image && (int)($product->base_image->id ?? 0) > 0) {
                return $product->base_image->thumb_webp_url
                    ?: $product->base_image->thumb_jpeg_url
                    ?: $product->base_image->url;
            }
        } catch (\Throwable $e) {}
        
        return url('build/assets/image-placeholder.png');
    }

    public function build()
    {
        $statusText = $this->isOutOfStock ? 'Stok Tükendi' : 'Düşük Stok Uyarısı';
        $subject = '⚠️ ' . $statusText . ': ' . $this->product->name;
        
        if ($this->variantName) {
            $subject .= ' (' . $this->variantName . ')';
        }

        return $this
            ->subject($subject)
            ->view('storefront::emails.admin_stock_alert');
    }
}

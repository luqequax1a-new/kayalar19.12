<?php

namespace Modules\Product\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

use Illuminate\Contracts\Queue\ShouldQueue;

class BackInStockMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Product $product;
    public ?ProductVariant $variant;

    public function __construct(Product $product, ?ProductVariant $variant = null)
    {
        $this->product = $product;
        $this->variant = $variant;
    }

    public function build()
    {
        return $this
            ->subject('Müjde! Beklediğiniz Ürün Tekrardan Stokta! 🥳')
            ->view('storefront::emails.back_in_stock');
    }
}

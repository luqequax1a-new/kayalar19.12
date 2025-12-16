<?php

namespace Modules\Product\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Product\Entities\Product;

class BackInStockMail extends Mailable
{
    use Queueable, SerializesModels;

    public Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function build()
    {
        return $this
            ->subject('Ürün tekrar stokta')
            ->view('storefront::emails.back_in_stock');
    }
}

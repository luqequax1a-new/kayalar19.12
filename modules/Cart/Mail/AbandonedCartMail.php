<?php

namespace Modules\Cart\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Cart\Entities\Cart;
use Modules\Coupon\Entities\Coupon;

class AbandonedCartMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cart;
    public $coupon;
    public $cartUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Cart $cart, ?Coupon $coupon = null)
    {
        $this->cart = $cart;
        $this->coupon = $coupon;
        $this->cartUrl = route('cart.track', ['id' => $cart->id, 'redirect_to' => route('cart.index')]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('storefront::checkout.abandoned_cart_mail_subject', ['store_name' => setting('store_name')]))
            ->view('storefront::emails.abandoned_cart');
    }
}

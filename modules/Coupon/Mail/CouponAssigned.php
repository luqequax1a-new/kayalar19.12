<?php

namespace Modules\Coupon\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Coupon\Entities\Coupon;
use Modules\User\Entities\User;

class CouponAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public $coupon;
    public $customer;

    /**
     * Create a new message instance.
     *
     * @param Coupon $coupon
     * @param User $customer
     * @return void
     */
    public function __construct(Coupon $coupon, User $customer)
    {
        $this->coupon = $coupon;
        $this->customer = $customer;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $isRtl = is_rtl();
        $template = $isRtl ? 'storefront::emails.coupon_assigned_rtl' : 'storefront::emails.coupon_assigned';

        $discountText = $this->coupon->is_percent 
            ? '%' . (int)$this->coupon->value 
            : $this->coupon->value->convertToCurrentCurrency()->format();

        return $this->subject("Size Özel {$discountText} İndirim Kuponu Tanımlandı! 🎉")
                    ->view($template)
                    ->with([
                        'coupon' => $this->coupon,
                        'customer' => $this->customer,
                    ]);
    }
}

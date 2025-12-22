<?php

namespace Modules\Order\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $heading;
    public $text;
    public $order;


    /**
     * Create a new message instance.
     *
     * @param Order $order
     *
     * @return void
     */
    public function __construct($order)
    {
        app()->setLocale($order->locale);

        $this->order = $order;
        $this->heading = $this->getHeading($order);
        $this->text = $this->getText($order);
        $extra = '';
        if ($order->shipping_tracking_number) {
            $extra .= ' ' . 'Takip No: ' . $order->shipping_tracking_number;
        }
        if ($order->shipping_carrier_name) {
            $extra .= ' ' . 'Kargo: ' . $order->shipping_carrier_name;
        }
        if ($extra !== '') {
            $this->text .= $extra;
        }
    }


    public function getHeading($order)
    {
        if ($order->status === Order::SHIPPED) {
            return 'Siparişiniz Kargoya Verildi 🚚';
        }
        return trans('storefront::mail.hello', ['name' => $order->customer_first_name]);
    }


    public function getText($order)
    {
        if ($order->status === Order::SHIPPED) {
            return 'Sipariş kargoya verildi, en kısa sürede teslim edilecek.';
        }
        return trans('order::mail.your_order_status_changed_text', [
            'order_id' => $order->displayOrderNumber(),
            'status' => mb_strtolower($order->status()),
        ]);
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = trans('order::mail.your_order_status_changed_subject');
        if ($this->order && $this->order->status === Order::COMPLETED) {
            $subject = setting('store_name') . ' – ' . 'Siparişiniz Teslim Edildi 🎁';
        } elseif ($this->order && $this->order->status === Order::CANCELED) {
            $subject = setting('store_name') . ' – ' . 'Siparişiniz İptal Edildi ❌';
        } elseif ($this->order && $this->order->status === Order::REFUNDED) {
            $subject = setting('store_name') . ' – ' . 'İadeniz Tamamlandı 💸';
        } elseif ($this->order && $this->order->status === Order::SHIPPED) {
            $subject = setting('store_name') . ' – ' . 'Siparişiniz Kargoya Verildi 🚚';
        }

        $actionUrl = null;
        if ($this->order && is_string($this->order->tracking_reference) && filter_var($this->order->tracking_reference, FILTER_VALIDATE_URL)) {
            $actionUrl = $this->order->tracking_reference;
        } elseif ($this->order && is_string($this->order->shipping_tracking_url) && filter_var($this->order->shipping_tracking_url, FILTER_VALIDATE_URL)) {
            $actionUrl = $this->order->shipping_tracking_url;
        }

        return $this->subject($subject)
            ->view("storefront::emails.{$this->getViewName()}", [
                'logo' => File::findOrNew(setting('storefront_mail_logo'))->path,
                'action_url' => $actionUrl,
                'action_text' => 'Kargoyu Takip Et',
            ]);
    }


    private function getViewName()
    {
        if ($this->order && $this->order->status === Order::COMPLETED) {
            return 'order_completed' . (is_rtl() ? '_rtl' : '');
        }
        if ($this->order && $this->order->status === Order::CANCELED) {
            return 'order_canceled' . (is_rtl() ? '_rtl' : '');
        }
        if ($this->order && $this->order->status === Order::REFUNDED) {
            return 'order_refunded' . (is_rtl() ? '_rtl' : '');
        }
        if ($this->order && $this->order->status === Order::SHIPPED) {
            return 'text' . (is_rtl() ? '_rtl' : '');
        }
        return 'text' . (is_rtl() ? '_rtl' : '');
    }
}

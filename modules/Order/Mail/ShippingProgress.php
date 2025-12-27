<?php

namespace Modules\Order\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;

class ShippingProgress extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        if (is_string($this->order->locale) && $this->order->locale !== '') {
            app()->setLocale($this->order->locale);
        }

        $this->order->loadMissing([
            'products.product.files',
            'products.product_variant.files',
            'products.variations.values',
            'products.options.option',
            'products.options.values',
            'shippingAddress',
            'billingAddress',
            'billingSnapshot',
            'shippingSnapshot',
        ]);

        $fmtMoney = function ($money) {
            if (function_exists('format_price')) {
                try {
                    return format_price($money);
                } catch (\Throwable $e) {
                    // fallback below
                }
            }

            if (is_numeric($money)) {
                return number_format((float) $money, 2, ',', '.') . ' ₺';
            }

            if (is_object($money)) {
                try {
                    if (method_exists($money, 'format')) {
                        return $money->format();
                    }
                    if (method_exists($money, '__toString')) {
                        return (string) $money;
                    }
                } catch (\Throwable $e) {
                    return null;
                }
            }

            return null;
        };

        $getAddr = function ($snapshot, $address, string $key, $default = null) {
            $val = data_get($snapshot, $key);
            if ($val === null || $val === '') {
                $val = data_get($address, $key);
            }

            return ($val === null || $val === '') ? $default : $val;
        };

        $homeUrl = \Route::has('home') ? route('home') : url('/');
        $categoriesUrl = \Route::has('categories.index') ? route('categories.index') : $homeUrl;
        $accountUrl = \Route::has('account.dashboard.index') ? route('account.dashboard.index') : $homeUrl;
        $registerUrl = \Route::has('register') ? route('register') : $homeUrl;

        return $this->subject($this->subjectForStatus($this->order))
            ->view('storefront::emails.shipping_progress', [
                'logo' => File::findOrNew(setting('storefront_mail_logo'))->path,
                'order' => $this->order,
                'products' => $this->order->products,
                'shippingSnapshot' => $this->order->shippingSnapshot,
                'billingSnapshot' => $this->order->billingSnapshot,
                'shippingAddress' => $this->order->shippingAddress,
                'billingAddress' => $this->order->billingAddress,
                'fmtMoney' => $fmtMoney,
                'getAddr' => $getAddr,
                'homeUrl' => $homeUrl,
                'categoriesUrl' => $categoriesUrl,
                'accountUrl' => $accountUrl,
                'registerUrl' => $registerUrl,
            ]);
    }

    private function subjectForStatus(Order $order): string
    {
        $storeName = (string) (setting('store_name') ?: config('app.name') ?: '');

        switch ($order->status) {
            case Order::SHIPPED:
                return $storeName . ' – ' . 'Siparişiniz Kargoya Verildi';
            case Order::ON_THE_WAY:
                return $storeName . ' – ' . 'Siparişiniz Yolda';
            case Order::OUT_FOR_DELIVERY:
                return $storeName . ' – ' . 'Siparişiniz Dağıtımda';
            case Order::COMPLETED:
                return $storeName . ' – ' . 'Siparişiniz Teslim Edildi';
            default:
                return $storeName . ' – ' . 'Sipariş Durumu';
        }
    }
}

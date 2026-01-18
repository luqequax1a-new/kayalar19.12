<?php

namespace Modules\Order\Entities;

use Modules\Cart\CartTax;
use Modules\Cart\CartItem;
use Modules\Support\Money;
use Modules\Support\State;
use Modules\Support\Country;
use Modules\Media\Entities\File;
use Modules\Tax\Entities\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Modules\Order\OrderCollection;
use Modules\Coupon\Entities\Coupon;
use Modules\Order\Admin\OrderTable;
use Modules\Support\Eloquent\Model;
use Modules\Payment\Facades\Gateway;
use Modules\Payment\HasTransactionReference;
use Modules\Shipping\Facades\ShippingMethod;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Transaction\Entities\Transaction;
use Modules\Order\Entities\OrderAddress;

class Order extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (self $order) {
            if (empty($order->order_number)) {
                $order->order_number = $order->generateOrderNumber();
            }
        });
    }

    const CANCELED = 'canceled';
    const COMPLETED = 'completed';
    const ON_THE_WAY = 'on_the_way';
    const OUT_FOR_DELIVERY = 'out_for_delivery';
    const PENDING = 'pending';
    const PENDING_PAYMENT = 'pending_payment';
    const REFUNDED = 'refunded';
    const SHIPPED = 'shipped';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'deleted_at' => 'datetime',
        'cod_fee' => 'decimal:4',
    ];


    public function displayOrderNumber(): string
    {
        return (string) ($this->order_number ?: $this->id);
    }


    private function generateOrderNumber(): string
    {
        for ($i = 0; $i < 20; $i++) {
            $candidate = 'KYM_' . (string) random_int(1000, 9999);

            $exists = self::query()
                ->where('order_number', $candidate)
                ->when($this->exists, fn ($q) => $q->where('id', '!=', $this->id))
                ->exists();

            if (!$exists) {
                return $candidate;
            }
        }

        // Extremely unlikely fallback, but guarantees uniqueness.
        return 'KYM_' . (string) $this->getKey() . '_' . (string) time();
    }


    public static function totalSales()
    {
        return Money::inDefaultCurrency(self::withoutCanceledOrders()->sum('total'));
    }


    public function status()
    {
        return trans("order::statuses.{$this->status}");
    }


    public function hasShippingMethod()
    {
        return !is_null($this->shipping_method);
    }


    public function hasCoupon()
    {
        return !is_null($this->coupon);
    }


    public function totalTax()
    {
        $total = 0;

        if ($this->hasTax()) {
            $this->taxes()
                ->get()
                ->each(function ($tax) use (&$total) {
                    $total += $tax->order_tax->amount->amount();
                });
        }

        return Money::inDefaultCurrency($total);
    }


    public function hasTax()
    {
        return $this->taxes->isNotEmpty();
    }


    public function taxes()
    {
        return $this->belongsToMany(TaxRate::class, 'order_taxes')
            ->using(OrderTax::class)
            ->as('order_tax')
            ->withPivot('amount')
            ->withTrashed();
    }


    public function salesAnalytics()
    {
        return $this->normalizeOrders($this->ordersByWeekDay())->mapWithKeys(function ($orders, $weekDay) {
            return [$weekDay => $this->dataForChart($orders)];
        });
    }


    public function coupon()
    {
        return $this->belongsTo(Coupon::class)->withTrashed();
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(\Modules\Address\Entities\Address::class, 'shipping_address_id');
    }

    public function billingAddress()
    {
        return $this->belongsTo(\Modules\Address\Entities\Address::class, 'billing_address_id');
    }

    public function orderAddresses()
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shippingSnapshot()
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'shipping');
    }

    public function billingSnapshot()
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'billing');
    }


    public function getSubTotalAttribute($subTotal)
    {
        return Money::inDefaultCurrency($subTotal);
    }


    public function getShippingCostAttribute($shippingCost)
    {
        return Money::inDefaultCurrency($shippingCost);
    }


    public function getDiscountAttribute($discount)
    {
        return Money::inDefaultCurrency($discount);
    }


    public function getTaxAttribute($tax)
    {
        return Money::inDefaultCurrency($tax);
    }


    public function getTotalAttribute($total)
    {
        return Money::inDefaultCurrency($total);
    }


    public function getCodFeeAttribute($codFee)
    {
        return Money::inDefaultCurrency($codFee);
    }


    /**
     * Get the order's shipping method.
     *
     * @param string $shippingMethod
     *
     * @return string
     */
    public function getShippingMethodAttribute($shippingMethod)
    {
        return ShippingMethod::get($shippingMethod)->label ?? null;
    }


    /**
     * Get the order's payment method.
     *
     * @param string $paymentMethod
     *
     * @return string
     */
    public function getPaymentMethodAttribute($paymentMethod)
    {
        return Gateway::get($paymentMethod)->label ?? '';
    }


    /**
     * Get the order's payment method label.
     *
     * @return string
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return $this->payment_method ?: '';
    }



    public function isCodPayment(): bool
    {
        return ($this->attributes['payment_method'] ?? null) === 'cod';
    }


    public function getCustomerFullNameAttribute()
    {
        return "{$this->customer_first_name} {$this->customer_last_name}";
    }


    public function getBillingFullNameAttribute()
    {
        $s = $this->billingSnapshot;
        if ($s && ($s->first_name || $s->last_name)) {
            return trim("{$s->first_name} {$s->last_name}");
        }
        $a = $this->billingAddress;
        if ($a && ($a->first_name || $a->last_name)) {
            return trim("{$a->first_name} {$a->last_name}");
        }
        return (string) ($this->customer_full_name ?? '');
    }


    public function getShippingFullNameAttribute()
    {
        $s = $this->shippingSnapshot;
        if ($s && ($s->first_name || $s->last_name)) {
            return trim("{$s->first_name} {$s->last_name}");
        }
        $a = $this->shippingAddress;
        if ($a && ($a->first_name || $a->last_name)) {
            return trim("{$a->first_name} {$a->last_name}");
        }
        return (string) ($this->customer_full_name ?? '');
    }


    public function getShippingPhoneAttribute(): ?string
    {
        return $this->shippingSnapshot?->phone
            ?? $this->shippingAddress?->phone
            ?? ($this->attributes['customer_phone'] ?? null);
    }


    public function getBillingPhoneAttribute(): ?string
    {
        return $this->billingSnapshot?->phone
            ?? $this->billingAddress?->phone
            ?? ($this->attributes['customer_phone'] ?? null);
    }


    public function getBillingCountryNameAttribute()
    {
        $country = $this->billingSnapshot?->country
            ?? $this->billingAddress?->country
            ?? null;
        return Country::name($country);
    }


    public function getShippingCountryNameAttribute()
    {
        $country = $this->shippingSnapshot?->country
            ?? $this->shippingAddress?->country
            ?? null;
        return Country::name($country);
    }


    public function getBillingStateNameAttribute()
    {
        $snap = $this->billingSnapshot;
        if ($snap && $snap->district) {
            return $this->formatState($snap->district);
        }
        $a = $this->billingAddress;
        if ($a && $a->state_name) {
            return $this->formatState($a->state_name);
        }
        return null;
    }


    public function getShippingStateNameAttribute()
    {
        $snap = $this->shippingSnapshot;
        if ($snap && $snap->district) {
            return $this->formatState($snap->district);
        }
        $a = $this->shippingAddress;
        if ($a && $a->state_name) {
            return $this->formatState($a->state_name);
        }
        return null;
    }


    public function getBillingCityTitleAttribute()
    {
        $snap = $this->billingSnapshot;
        if ($snap && $snap->city) {
            return $this->formatState($snap->city);
        }
        $a = $this->billingAddress;
        if ($a && $a->city_title) {
            return $this->formatState($a->city_title);
        }
        return null;
    }


    public function getShippingCityTitleAttribute()
    {
        $snap = $this->shippingSnapshot;
        if ($snap && $snap->city) {
            return $this->formatState($snap->city);
        }
        $a = $this->shippingAddress;
        if ($a && $a->city_title) {
            return $this->formatState($a->city_title);
        }
        return null;
    }


    public function scopeWithoutCanceledOrders($query)
    {
        return $query->whereNotIn('status', [self::CANCELED, self::REFUNDED]);
    }


    public function storeProducts(CartItem $cartItem)
    {
        $unit = $cartItem->product->getEffectiveUnit();
        $imagePath = $cartItem->variant?->base_image?->path ?? $cartItem->product->base_image?->path ?? null;
        $orderProduct = $this->products()->create([
            'product_id' => $cartItem->product->id,
            'product_variant_id' => $cartItem->variant?->id,
            'unit_price' => $cartItem->unitPrice()->amount(),
            'unit_price_at_order' => $cartItem->unitPrice()->amount(),
            'qty' => $cartItem->qty,
            'line_total' => $cartItem->totalPrice()->amount(),
            'unit_code' => $unit->code ?? null,
            'unit_label' => $unit->label ?? null,
            'unit_short_suffix' => $unit->short_suffix ?? null,
            'product_name' => $cartItem->product->name,
            'product_slug' => $cartItem->product->slug,
            'product_sku' => $cartItem->variant?->sku ?? $cartItem->product->sku,
            'product_image_path' => $imagePath,
            'is_upsell' => $cartItem->upsell['is_upsell'] ?? false,
            'upsell_data' => $cartItem->upsell ?? null,
        ]);

        $orderProduct->storeVariations($cartItem->variations);
        $orderProduct->storeOptions($cartItem->options);
    }


    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }


    public function storeDownloads(CartItem $cartItem)
    {
        $cartItem->product->downloads->each(function (File $file) {
            $this->downloads()->create(['file_id' => $file->id]);
        });
    }


    public function downloads()
    {
        return $this->hasMany(OrderDownload::class);
    }


    public function attachTax(CartTax $cartTax)
    {
        $this->taxes()->attach($cartTax->id(), ['amount' => $cartTax->amount()->amount()]);
    }


    public function storeTransaction($response)
    {
        if (!$response instanceof HasTransactionReference) {
            return;
        }

        $this->transaction()->create([
            'transaction_id' => $response->getTransactionReference(),
            'payment_method' => $this->attributes['payment_method'],
        ]);
    }


    public function transaction()
    {
        return $this->hasOne(Transaction::class)->withTrashed();
    }


    /**
     * Get table data for the resource
     *
     * @return JsonResponse
     */
    public function table(?Request $request = null)
    {
        $request ??= request();

        $query = $this->newQuery()->select([
            'id',
            'order_number',
            'customer_first_name',
            'customer_last_name',
            'customer_email',
            'sub_total',
            'shipping_method',
            'shipping_cost',
            'coupon_code',
            'discount',
            'payment_method',
            'currency',
            'currency_rate',
            'total',
            'status',
            'created_at',
        ]);

        $source = $request?->query('traffic_source');

        if (is_string($source) && trim($source) !== '') {
            $source = strtolower(trim($source));

            if ($source === 'other') {
                $query->where(function ($q) {
                    $q->whereNull('traffic_source')
                        ->orWhere('traffic_source', '')
                        ->orWhere('traffic_source', 'other');
                });
            } else {
                $query->where('traffic_source', $source);
            }
        }

        return new OrderTable($query->with(['products', 'taxes', 'billingSnapshot', 'shippingSnapshot', 'billingAddress', 'shippingAddress']));
    }


    private function normalizeOrders($orders)
    {
        return Collection::times(7)->map(function ($dayOfWeek) use ($orders) {
            return new OrderCollection($orders[now()->subDays(7 - $dayOfWeek)->weekday()] ?? []);
        });
    }


    private function ordersByWeekDay()
    {
        return self::select('total', 'created_at')
            ->withoutCanceledOrders()
            ->whereBetween('created_at', [now()->subDays(6), now()->addDay()])
            ->get()
            ->reduce(function ($ordersByWeekDay, $order) {
                $ordersByWeekDay[$order->created_at->weekday()][] = $order;

                return $ordersByWeekDay;
            });
    }


    private function dataForChart(OrderCollection $orders)
    {
        return [
            'total' => $orders->sumTotal(),
            'total_orders' => $orders->count(),
        ];
    }
    
    public function transitionTo(string $status): void
    {
        if ($status === $this->status) {
            return;
        }
        $fromStatus = $this->status;

        $source = null;
        $context = null;
        try {
            $req = request();
            if ($req) {
                $source = $req->attributes->get('order_status_change_source');
                $context = $req->attributes->get('order_status_change_context');
            }
        } catch (\Throwable $e) {
        }

        $this->update(['status' => $status]);
        event(new \Modules\Order\Events\OrderStatusChanged($this, $fromStatus, $status, is_string($source) ? $source : null, is_array($context) ? $context : null));
    }
    
    private function formatState($name)
    {
        if ($name === null || $name === '') {
            return $name;
        }
        $mapUpperToLower = [
            'I' => 'ı',
            'İ' => 'i',
            'Ç' => 'ç',
            'Ş' => 'ş',
            'Ğ' => 'ğ',
            'Ü' => 'ü',
            'Ö' => 'ö',
        ];
        $s = strtr($name, $mapUpperToLower);
        $s = mb_strtolower($s, 'UTF-8');
        $parts = preg_split('/([\s\-]+)/u', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
        $mapLowerToUpper = [
            'i' => 'İ',
            'ı' => 'I',
            'ç' => 'Ç',
            'ş' => 'Ş',
            'ğ' => 'Ğ',
            'ü' => 'Ü',
            'ö' => 'Ö',
        ];
        $res = '';
        foreach ($parts as $idx => $p) {
            if ($idx % 2 === 0) {
                if ($p === '') {
                    $res .= $p;
                    continue;
                }
                $first = mb_substr($p, 0, 1, 'UTF-8');
                $rest = mb_substr($p, 1, null, 'UTF-8');
                $firstU = $mapLowerToUpper[$first] ?? mb_strtoupper($first, 'UTF-8');
                $res .= $firstU . $rest;
            } else {
                $res .= $p;
            }
        }
        return $res;
    }
}

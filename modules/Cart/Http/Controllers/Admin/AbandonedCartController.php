<?php

namespace Modules\Cart\Http\Controllers\Admin;

use Modules\Cart\Entities\Cart;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Cart\Http\Requests\SaveCartRequest;

class AbandonedCartController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Cart::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'cart::abandoned_carts.abandoned_cart';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'cart::admin.abandoned_carts';

    /**
     * Form requests for the resource.
     *
     * @var array
     */
    protected $validation = SaveCartRequest::class;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->has('chart')) {
            return response()->json($this->getChartData());
        }

        $stats = $this->getStats();
        
        if (request()->has('table')) {
            return $this->getTable();
        }

        return view("{$this->viewPath}.index", compact('stats'));
    }

    /**
     * Get statistics for abandoned carts.
     *
     * @return array
     */
    private function getStats()
    {
        $range = request('days', '30');
        $start = $this->getStartDate($range);
        
        // Only count _cart_items to avoid double counting
        $totalAbandoned = Cart::where('is_recovered', false)
            ->where('id', 'like', '%_cart_items')
            ->when($start, function($q) use ($start) {
                return $q->where('updated_at', '>=', $start);
            })
            ->whereNotNull('data')
            ->where('data', '!=', 'a:0:{}')
            ->where('data', '!=', 'b:0;')
            ->count();

        // Count unique recovered orders
        $totalRecovered = Cart::where('is_recovered', true)
            ->where('id', 'like', '%_cart_items')
            ->whereNotNull('order_id')
            ->when($start, function($q) use ($start) {
                return $q->where('recovered_at', '>=', $start);
            })
            ->distinct('order_id')
            ->count('order_id');

        // Sum revenue from unique orders
        $recoveredCarts = Cart::where('is_recovered', true)
            ->where('id', 'like', '%_cart_items')
            ->whereNotNull('order_id')
            ->when($start, function($q) use ($start) {
                return $q->where('recovered_at', '>=', $start);
            })
            ->with('order')
            ->get();
        
        $recoveredRevenue = $recoveredCarts
            ->unique('order_id')
            ->sum(function ($cart) {
                return (float) (optional($cart->order)->total->amount() ?? 0);
            });

        $clickedCarts = Cart::where('is_clicked', true)
            ->where('id', 'like', '%_cart_items')
            ->when($start, function($q) use ($start) {
                return $q->where('clicked_at', '>=', $start);
            })
            ->count();

        $recoveryRate = $totalAbandoned > 0 
            ? round(($totalRecovered / $totalAbandoned) * 100, 2) 
            : 0;

        return [
            'total_abandoned' => $totalAbandoned,
            'total_recovered' => $totalRecovered,
            'recovered_revenue' => \Modules\Support\Money::inDefaultCurrency($recoveredRevenue)->format(),
            'clicked_carts' => $clickedCarts,
            'recovery_rate' => $recoveryRate,
            'range' => $range,
        ];
    }

    private function getStartDate($range)
    {
        return match($range) {
            'today' => now()->startOfDay(),
            'yesterday' => now()->subDay()->startOfDay(),
            'week' => now()->subDays(7)->startOfDay(),
            'month' => now()->subDays(30)->startOfDay(),
            'all' => null,
            default => now()->subDays((int)$range)->startOfDay(),
        };
    }

    /**
     * Get table data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function getTable()
    {
        $query = Cart::with(['order'])
            ->where('id', 'like', '%_cart_items');
        
        $range = request('days', '30');
        $start = $this->getStartDate($range);

        // Filter by tab
        if (request()->has('tab') && request('tab') === 'recovered') {
            $query->where('is_recovered', true)
                  ->whereNotNull('order_id')
                  ->when($start, function($q) use ($start) {
                      return $q->where('recovered_at', '>=', $start);
                  });
        } else {
            $query->where('is_recovered', false)
                  ->where(function($q) {
                      $q->where('data', '!=', 'a:0:{}')
                        ->where('data', '!=', 'b:0;')
                        ->whereNotNull('data');
                  })
                  ->when($start, function($q) use ($start) {
                      return $q->where('updated_at', '>=', $start);
                  });
        }
        
        // Order by appropriate date field based on tab
        if (request()->has('tab') && request('tab') === 'recovered') {
            $query->orderBy('recovered_at', 'desc');
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        return datatables()
            ->eloquent($query)
            ->editColumn('customer_email', function ($cart) {
                $email = $cart->customer_email ?: '-';
                
                if ($cart->is_recovered && $cart->recovered_by_email && $cart->recovered_by_email !== $cart->customer_email) {
                    $email .= ' <br><small class="text-muted" title="Kurtarılan E-posta" style="display:block; font-size:10px; color:#10b981; font-weight:600;">(Kurtaran: ' . $cart->recovered_by_email . ')</small>';
                }
                
                return $email;
            })
            ->editColumn('customer_name', function ($cart) {
                $firstName = $cart->customer_first_name ?? '';
                $lastName = $cart->customer_last_name ?? '';
                $name = trim($firstName . ' ' . $lastName);
                
                // If no name, use email prefix
                if (empty($name)) {
                    $emailParts = explode('@', $cart->customer_email ?? '');
                    $name = $emailParts[0] ?? 'Misafir';
                }
                
                if ($cart->is_recovered && $cart->recovered_by_email && $cart->recovered_by_email !== $cart->customer_email) {
                    $name .= ' <small class="text-muted" style="display:block; font-size:10px; color:#1890ff;">(Sipariş: ' . explode('@', $cart->recovered_by_email)[0] . ')</small>';
                }
                
                return $name;
            })
            ->editColumn('items_count', function ($cart) {
                $items = $this->extractCartItems($cart->data);

                return count($items);
            })
            ->addColumn('cart_total', function ($cart) {
                // If the cart is recovered and has an associated order, show the order total
                if ($cart->is_recovered && $cart->order) {
                    $formattedTotal = $cart->order->total->format();
                    return '<div class="total-display"><strong>' . $formattedTotal . '</strong><span class="badge-minimal success" style="font-size: 10px; padding: 0 4px; margin-top:2px;">Sipariş</span></div>';
                }

                $total = 0;
                try {
                    $items = $this->extractCartItems($cart->data);

                    foreach ($items as $item) {
                        try {
                            $itemObject = $this->normalizeCartItem($item);

                            if ($itemObject === null) {
                                continue;
                            }

                            $cartItem = new \Modules\Cart\CartItem($itemObject);
                            $total += $cartItem->totalPrice()->amount();
                        } catch (\Exception $e) {
                            // Skip invalid items
                        }
                    }
                } catch (\Exception $e) {
                    // Skip calculation errors
                }
                
                $formattedTotal = \Modules\Support\Money::inDefaultCurrency($total)->format();
                return '<div class="total-display"><strong>' . $formattedTotal . '</strong></div>';
            })
            ->addColumn('cart_items_html', function ($cart) {
                $items = $this->extractCartItems($cart->data);

                if (empty($items)) {
                    return '';
                }

                $itemsCount = count($items);

                $html = '<div class="cart-items-tooltip-container">';
                $html .= '<div class="cart-items-tooltip-header">';
                $html .= '<div class="cart-items-tooltip-title">Sepetteki Ürünler</div>';
                $html .= '<div class="cart-items-tooltip-count">' . $itemsCount . ' ürün</div>';
                $html .= '</div>';
                $html .= '<div class="cart-items-tooltip-body">';
                foreach ($items as $item) {
                    try {
                        $itemObject = $this->normalizeCartItem($item);

                        if ($itemObject === null) {
                            continue;
                        }

                        $cartItem = new \Modules\Cart\CartItem($itemObject);
                        $product = $cartItem->product;
                        $variant = $cartItem->variant;
                        
                        // Check if this is an upsell item
                        $isUpsell = !empty($cartItem->upsell) && isset($cartItem->upsell['is_upsell']) && $cartItem->upsell['is_upsell'];

                        $nameFromItem = null;

                        if (is_object($item) && isset($item->name)) {
                            $nameFromItem = $item->name;
                        } elseif (is_array($item) && isset($item['name'])) {
                            $nameFromItem = $item['name'];
                        }

                        $displayName = $product?->name ?: ($variant?->name ?: ($nameFromItem ?: 'Ürün'));
                        
                        // Get image
                        $image = optional($variant?->base_image)->path ?? optional($product?->base_image)->path ?? '';
                        
                        // Get variation labels (more readable)
                        $variantName = '';
                        if ($cartItem->variations->isNotEmpty()) {
                            $variantParts = [];
                            foreach ($cartItem->variations as $variation) {
                                $values = $variation->values->pluck('label')->implode(', ');
                                $label = trim(($variation->name ?? '') . ': ' . $values);
                                $variantParts[] = $label !== ':' ? $label : $values;
                            }
                            $variantName = implode(' • ', array_filter($variantParts));
                        }
                        
                        $html .= '<div class="cart-item-row' . ($isUpsell ? ' upsell-item' : '') . '">';
                        $html .= '<div class="cart-item-image">';
                        if ($image) {
                            $html .= '<img src="' . e($image) . '" alt="' . e($displayName) . '">';
                        } else {
                            $html .= '<div class="no-image"><i class="fa fa-image"></i></div>';
                        }
                        $html .= '</div>';
                        
                        // Get unit suffix
                        $qty = (float)($itemObject->quantity ?? 0);
                        $qtyValue = fmod($qty, 1) === 0.0 ? (int)$qty : rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
                        $unitSuffix = $product ? ($product->unit_suffix ?? '') : '';
                        $qtyDisplay = $unitSuffix ? trim($qtyValue . ' ' . $unitSuffix) : $qtyValue . ' Adet';
                        
                        $html .= '<div class="cart-item-details">';
                        
                        // Add upsell badge if applicable
                        if ($isUpsell) {
                            $html .= '<span class="upsell-badge">🎁 Sepet Teklifi</span>';
                        }

                        $variantInline = $variant?->name ?: $variantName;
                        $title = $variantInline ? ($displayName . ' - ' . $variantInline) : $displayName;

                        $html .= '<div class="cart-item-name">' . e($title) . '</div>';
                        $html .= '<div class="cart-item-meta">';
                        $html .= '<span class="cart-item-qty">' . e($qtyDisplay) . '</span>';

                        $html .= '<span class="cart-item-price' . ($isUpsell ? ' upsell-price' : '') . '"><span class="cart-item-price-label">Toplam</span>' . e($cartItem->totalPrice()->format()) . '</span>';
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                    } catch (\Exception $e) {
                        // Skip invalid items but continue
                        \Illuminate\Support\Facades\Log::warning('Failed to render cart item in HTML', [
                            'cart_id' => $cart->id,
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }

                $html .= '</div>';
                $html .= '</div>';
                
                return $html;
            })
            ->editColumn('is_recovered', function ($cart) {
                if ($cart->is_recovered && $cart->order_id) {
                    return '<span class="badge badge-success">Sipariş Verildi</span>';
                } elseif ($cart->is_clicked && $cart->clicked_at) {
                    return '<span class="badge badge-info">Tıklandı</span>';
                } elseif ($cart->reminder_count > 0) {
                    return '<span class="badge badge-warning">Gönderildi</span>';
                } else {
                    return '<span class="badge badge-secondary">Beklemede</span>';
                }
            })
            ->editColumn('is_clicked', function ($cart) {
                // Show click count instead of just badge
                if ($cart->is_clicked && $cart->clicked_at) {
                    return '<span class="badge badge-info" title="' . $cart->clicked_at->format('d.m.Y H:i') . '">✓ Evet</span>';
                } else {
                    return '<span class="badge badge-secondary">-</span>';
                }
            })
            ->editColumn('reminder_count', function ($cart) {
                $count = $cart->reminder_count ?? 0;
                if ($count > 0) {
                    return '<span class="reminder-count">' . $count . ' kez</span>';
                }
                return '<span class="reminder-count">-</span>';
            })
            // FIXED: Remove duplicate cart_total column - it was defined twice!
            ->editColumn('updated_at', function ($cart) {
                $date = $cart->is_recovered && $cart->recovered_at ? $cart->recovered_at : $cart->updated_at;
                return $date->format('d.m.Y H:i');
            })
            ->addColumn('actions', function ($cart) {
                return view('cart::admin.abandoned_carts.partials.actions', compact('cart'));
            })
            ->rawColumns(['is_recovered', 'is_clicked', 'reminder_count', 'updated_at', 'cart_total', 'cart_items_html', 'actions', 'customer_email'])
            ->with(['stats' => $this->getStats()])
            ->make(true);
    }

    private function extractCartItems($data): array
    {
        if ($data === null) {
            return [];
        }

        if (is_array($data)) {
            if (array_key_exists('items', $data) && is_array($data['items'])) {
                return $data['items'];
            }

            return $data;
        }

        if (is_object($data)) {
            if ($data instanceof \Illuminate\Support\Collection) {
                return $data->all();
            }

            if ($data instanceof \Traversable) {
                return iterator_to_array($data);
            }

            if (method_exists($data, 'all')) {
                try {
                    $all = $data->all();

                    return is_array($all) ? $all : [];
                } catch (\Throwable $e) {
                    return [];
                }
            }
        }

        return [];
    }

    private function normalizeCartItem($item)
    {
        if ($item === null) {
            return null;
        }

        if (is_array($item)) {
            $itemObject = (object) $item;

            if (isset($itemObject->attributes) && is_array($itemObject->attributes)) {
                if (isset($itemObject->attributes['options']) && is_array($itemObject->attributes['options'])) {
                    $itemObject->attributes['options'] = collect($itemObject->attributes['options']);
                }
                if (isset($itemObject->attributes['variations']) && is_array($itemObject->attributes['variations'])) {
                    $itemObject->attributes['variations'] = collect($itemObject->attributes['variations']);
                }
            }

            return $itemObject;
        }

        if (is_object($item)) {
            if (isset($item->attributes) && is_array($item->attributes)) {
                if (isset($item->attributes['options']) && is_array($item->attributes['options'])) {
                    $item->attributes['options'] = collect($item->attributes['options']);
                }
                if (isset($item->attributes['variations']) && is_array($item->attributes['variations'])) {
                    $item->attributes['variations'] = collect($item->attributes['variations']);
                }
            }

            return $item;
        }

        return null;
    }

    /**
     * Get chart data for the last 14 days.
     *
     * @return array
     */
    private function getChartData()
    {
        $days = 14;
        $labels = [];
        $abandonedData = [];
        $recoveredData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');

            // FIXED: Only count _cart_items for abandoned carts
            $abandoned = Cart::where('is_recovered', false)
                ->where('id', 'like', '%_cart_items')
                ->where('data', '!=', 'a:0:{}')
                ->where('data', '!=', 'b:0;')
                ->whereNotNull('data')
                ->whereDate('updated_at', $date->format('Y-m-d'))
                ->count();

            // FIXED: Only count _cart_items for recovered carts
            $recovered = Cart::where('is_recovered', true)
                ->where('id', 'like', '%_cart_items')
                ->whereNotNull('order_id')
                ->whereDate('recovered_at', $date->format('Y-m-d'))
                ->count();

            $abandonedData[] = $abandoned;
            $recoveredData[] = $recovered;
        }

        return [
            'labels' => $labels,
            'abandoned' => $abandonedData,
            'recovered' => $recoveredData,
        ];
    }

    /**
     * Show the details of a specific abandoned cart.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cart = Cart::with(['order'])->findOrFail($id);
        
        return view("{$this->viewPath}.show", compact('cart'));
    }

    /**
     * Send manual reminder for a specific cart.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendReminder($id)
    {
        try {
            $cart = Cart::findOrFail($id);

            if (!$cart->customer_email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu sepette e-posta adresi bulunmuyor!'
                ], 400);
            }

            if ($cart->is_recovered) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu sepet zaten kurtarılmış!'
                ], 400);
            }

            // Generate coupon if enabled
            $coupon = null;
            if (setting('abandoned_cart_coupon_enabled')) {
                $coupon = $this->getOrCreateCoupon($cart);
            }

            // Send email
            \Illuminate\Support\Facades\Mail::to($cart->customer_email)
                ->send(new \Modules\Cart\Mail\AbandonedCartMail($cart, $coupon));

            // Update reminder count
            $cart->update([
                'last_notified_at' => now(),
                'reminder_count' => ($cart->reminder_count ?? 0) + 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hatırlatma e-postası başarıyla gönderildi!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hata: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get or create coupon for abandoned cart.
     *
     * @param Cart $cart
     * @return \Modules\Coupon\Entities\Coupon|null
     */
    private function getOrCreateCoupon($cart)
    {
        // Invalidate any old coupons for this cart
        \Modules\Coupon\Entities\Coupon::where('cart_id', $cart->id)
            ->where('is_abandoned_cart_coupon', true)
            ->update(['is_active' => false]);

        // Always create a new coupon with current settings
        $discount = (int) setting('abandoned_cart_coupon_discount_percent', 10);
        $validDays = (int) setting('abandoned_cart_coupon_valid_days', 3);
        $code = 'SEPET-' . strtoupper(\Illuminate\Support\Str::random(6));

        return \Modules\Coupon\Entities\Coupon::create([
            locale() => ['name' => 'Terk Edilmiş Sepet İndirimi'],
            'code' => $code,
            'is_percent' => true,
            'value' => $discount,
            'free_shipping' => false,
            'is_active' => true,
            'start_date' => now(),
            'end_date' => now()->addDays($validDays),
            'usage_limit_per_coupon' => 1,
            'usage_limit_per_customer' => 1,
            'is_abandoned_cart_coupon' => true,
            'cart_id' => $cart->id,
            'customer_id' => $cart->user_id,
        ]);
    }

    /**
     * Clear all abandoned cart data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        try {
            \Modules\Cart\Entities\Cart::where('id', 'like', '%_cart%')->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Tüm sepet verileri başarıyla temizlendi!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Temizleme hatası: ' . $e->getMessage()
            ], 500);
        }
    }
}

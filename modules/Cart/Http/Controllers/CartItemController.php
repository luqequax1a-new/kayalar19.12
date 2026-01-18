<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Cart\Facades\Cart;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Checkers\ValidCoupon;
use Modules\Coupon\Checkers\MaximumSpend;
use Modules\Coupon\Checkers\MinimumSpend;
use Modules\Coupon\Checkers\CouponExists;
use Modules\Coupon\Checkers\AlreadyApplied;
use Modules\Coupon\Checkers\ExcludedProducts;
use Modules\Coupon\Checkers\ApplicableProducts;
use Modules\Coupon\Checkers\ExcludedCategories;
use Modules\Coupon\Checkers\UsageLimitPerCoupon;
use Modules\Cart\Http\Middleware\CheckItemStock;
use Modules\Coupon\Checkers\ApplicableCategories;
use Modules\Coupon\Checkers\UsageLimitPerCustomer;
use Modules\Cart\Http\Requests\StoreCartItemRequest;

class CartItemController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(CheckItemStock::class)
            ->only(['store', 'update']);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCartItemRequest $request
     *
     * @return \Modules\Cart\Cart
     */
    public function store(StoreCartItemRequest $request)
    {
        Cart::store(
            $request->product_id,
            $request->variant_id,
            $request->qty,
            $request->options ?? [],
        );

        return Cart::instance();
    }


    /**
     * Store an upsell item in the cart with a direct line discount.
     *
     * This does not apply any coupons; pricing is resolved purely
     * from the configured upsell rule on the backend.
     */
    public function storeUpsell(Request $request)
    {
        $data = $request->validate([
            'rule_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'variant_id' => ['nullable', 'integer'],
            'placement' => ['nullable', 'string'],
            'qty' => ['nullable'], // metre/adet desteği için numeric serbest bırakıyoruz
            'options' => ['nullable', 'array'],
        ]);

        $cart = Cart::instance();

        $cart->storeUpsell($data);

        // Track upsell usage
        $this->trackUpsellUsage($data['rule_id'], $data['product_id'], $data['qty'] ?? 1);

        return $cart;
    }

    /**
     * Track upsell rule usage when added to cart
     */
    protected function trackUpsellUsage($ruleId, $productId, $qty)
    {
        try {
            $rule = \Modules\Cart\Entities\CartUpsellRule::find($ruleId);
            if ($rule) {
                $rule->increment('times_added_to_cart', 1);
                
                // Calculate revenue based on discount
                $product = \Modules\Product\Entities\Product::find($productId);
                if ($product) {
                    $price = $product->selling_price->amount();
                    $discount = 0;
                    
                    if ($rule->discount_type === 'percent') {
                        $discount = $price * ($rule->discount_value / 100);
                    } elseif ($rule->discount_type === 'fixed') {
                        $discount = $rule->discount_value;
                    }
                    
                    $revenue = $discount * $qty;
                    $rule->increment('total_revenue', $revenue);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Upsell tracking error: ' . $e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param string $id
     *
     * @return \Modules\Cart\Cart
     */
    public function update(string $id)
    {
        Cart::updateQuantity($id, request('qty'));

        return Cart::instance();
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     *
     * @return \Modules\Cart\Cart
     */
    public function destroy(string $id)
    {
        Cart::remove($id);

        return Cart::instance();
    }
}

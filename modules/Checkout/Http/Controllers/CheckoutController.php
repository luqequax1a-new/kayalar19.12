<?php

namespace Modules\Checkout\Http\Controllers;

use Exception;
use Modules\Support\Country;
use Modules\Cart\Facades\Cart;
use Modules\Page\Entities\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Modules\Payment\Facades\Gateway;
use Illuminate\Contracts\View\Factory;
use Modules\Coupon\Checkers\ValidCoupon;
use Modules\Coupon\Checkers\CouponExists;
use Modules\Coupon\Checkers\MinimumSpend;
use Modules\Coupon\Checkers\MaximumSpend;
use Modules\User\Services\CustomerService;
use Modules\Checkout\Services\OrderService;
use Modules\Coupon\Checkers\AlreadyApplied;
use Modules\Coupon\Checkers\ExcludedProducts;
use Modules\Coupon\Checkers\ApplicableProducts;
use Modules\Coupon\Checkers\ExcludedCategories;
use Illuminate\Contracts\Foundation\Application;
use Modules\Coupon\Checkers\UsageLimitPerCoupon;
use Modules\Coupon\Checkers\ApplicableCategories;
use Modules\Order\Http\Requests\CheckoutAddressRequest;
use Modules\Coupon\Checkers\UsageLimitPerCustomer;
use Modules\Cart\Http\Middleware\CheckCartItemsStock;
use Modules\Cart\Http\Middleware\RedirectIfCartIsEmpty;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Modules\Shipping\Facades\ShippingMethod as ShippingMethodFacade;
use Modules\Shipping\SmartShippingCod;
use Modules\Shipping\Services\SmartShippingCalculator;
use Modules\Shipping\Method as ShippingMethod;
use Illuminate\Support\Facades\DB;
use Modules\Cart\Services\CartUpsellService;
use Modules\Address\Entities\DefaultAddress;

class CheckoutController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware([
            RedirectIfCartIsEmpty::class,
        ]);

        $this->middleware([
            CheckCartItemsStock::class,
        ])->only('store');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreOrderRequest $request
     * @param CustomerService $customerService
     * @param OrderService $orderService
     *
     * @return JsonResponse
     */
    public function store(CheckoutAddressRequest $request, CustomerService $customerService, OrderService $orderService)
    {
        if (auth()->guest() && $request->create_an_account) {
            $customerService->register($request)->login();
        }

        $order = $orderService->create($request);
        
        $gateway = Gateway::get($request->payment_method);

        try {
            $response = $gateway->purchase($order, $request);
        } catch (Exception $e) {
            $orderService->delete($order);

            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        $redirectTo = is_array($response) && array_key_exists('redirectUrl', $response)
            ? $response['redirectUrl']
            : route('checkout.complete.store', ['orderId' => $order->id, 'paymentMethod' => $request->payment_method]);

        return response()->json([
            'redirectUrl' => $redirectTo,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(CartUpsellService $upsellService): View|Factory|Application
    {
        Cart::clearCartConditions();

        $cart = Cart::instance();
        $cart->loadStockAndRelations();
        $upsellData = $upsellService->resolveBestRule($cart);

        return view('storefront::public.checkout.create', [
            'cart' => $cart,
            'upsellData' => $upsellData,
            'countries' => Country::supported(),
            'gateways' => Gateway::all(),
            'defaultAddress' => auth()->user()->defaultAddress ?? new DefaultAddress,
            'addresses' => $this->getAddresses(),
            'termsPageURL' => Page::urlForPage(setting('storefront_terms_page')),
            'availableCoupons' => $availableCoupons = \Modules\Coupon\Entities\Coupon::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('customer_id')
                        ->orWhere('customer_id', auth()->id());
                })
                ->where(function ($query) {
                    $query->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->where('show_in_checkout', true)
                ->orderBy('end_date', 'asc')
                ->get()
                ->filter(function ($coupon) {
                    if ($coupon->is_review_coupon || $coupon->is_abandoned_cart_coupon) {
                        return !$coupon->isRedeemed();
                    }
                    return !$coupon->usageLimitReached(auth()->user()->email ?? null);
                }),
            'hasPersonalCoupons' => auth()->check() && $availableCoupons->contains(fn($c) => !is_null($c->customer_id)),
            'couponCount' => $availableCoupons->count(),
        ]);
    }


    public function update(Request $request): JsonResponse
    {
        $start = microtime(true);
        DB::enableQueryLog();
        try {
            $shippingName = $request->input('shipping_method');
            $paymentName = $request->input('payment_method') ?? session('checkout.payment_method');

            Cart::instance()->loadStockAndRelations();
            $this->syncCustomerInfoToCart($request);

            if ($shippingName) {
                if ($shippingName === 'smart_shipping') {
                    /** @var SmartShippingCalculator $calculator */
                    $calculator = app(SmartShippingCalculator::class);

                    $label = setting('smart_shipping_name') ?: 'Standard Shipping';
                    $cost = $calculator->costForCurrentCart();

                    Cart::addShippingMethod(new ShippingMethod('smart_shipping', $label, $cost->amount()));
                } else {
                    Cart::addShippingMethod(ShippingMethodFacade::get($shippingName));
                }
                session(['checkout.shipping_method' => $shippingName]);
            }

            if ($paymentName) {
                $gateways = Gateway::all();
                $paymentName = $gateways->has($paymentName) ? $paymentName : $gateways->keys()->first();
                session(['checkout.payment_method' => $paymentName]);
            }

            $userKey = auth()->check() ? ('u:' . auth()->id()) : ('s:' . $request->session()->getId());
            $cartHash = md5(json_encode(Cart::items()->map(fn($ci) => [
                'p' => optional($ci->product)->id,
                'v' => optional($ci->variant)->id,
                'q' => $ci->qty,
            ])->values()->all()));

            $shippingKey = implode(':', ['checkout','shipping_methods',$userKey,$cartHash, currency()]);
            $paymentKey = implode(':', ['checkout','payment_methods',$userKey, currency()]);

            $shippingMethods = Cache::remember($shippingKey, 600, function () {
                $methods = ShippingMethodFacade::available();

                if (setting('smart_shipping_enabled') && $methods->has('smart_shipping')) {
                    /** @var SmartShippingCalculator $calculator */
                    $calculator = app(SmartShippingCalculator::class);

                    $methods = $methods->map(function ($method) use ($calculator) {
                        if ($method->name !== 'smart_shipping') {
                            return $method;
                        }

                        $cost = $calculator->costForCurrentCart();

                        return new ShippingMethod($method->name, $method->label, $cost->amount());
                    });
                }

                return $methods;
            });

            $allGateways = Gateway::all();

            if ($allGateways->has('cod') && !SmartShippingCod::allowedForCurrentCart()) {
                $allGateways->forget('cod');
            }

            // Ödeme yöntemlerini cache'lemeden, her istek için anlık olarak oluştur.
            // Böylece COD'un görünürlüğü ve açıklamasındaki ücret bilgisi, güncel
            // sepet tutarı ve kurallara göre her zaman doğru olur.
            $paymentMethods = $allGateways->map(function ($gateway, $code) {
                return [
                    'code' => $code,
                    'label' => property_exists($gateway, 'label') ? $gateway->label : '',
                    'description' => property_exists($gateway, 'description') ? $gateway->description : '',
                    'instructions' => property_exists($gateway, 'instructions') ? $gateway->instructions : '',
                ];
            });

            Cart::removeCodFee();

            if ($paymentName === 'cod') {
                $codFee = SmartShippingCod::codFeeForCurrentCart();
                Cart::addCodFee($codFee);
            }

            $cart = Cart::instance()->toArray();

            $totals = [
                'sub_total' => Cart::subTotal(),
                'shipping_cost' => Cart::shippingCost(),
                'discount' => Cart::discount(),
                'tax' => Cart::tax(),
                'total' => Cart::total(),
            ];

            return response()->json([
                'cart' => $cart,
                'totals' => $totals,
                'shipping_methods' => $shippingMethods,
                'payment_methods' => $paymentMethods,
                'selected' => [
                    'shipping_method' => session('checkout.shipping_method'),
                    'payment_method' => session('checkout.payment_method'),
                ],
            ]);
        } finally {
            DB::disableQueryLog();
        }
    }


    /**
     * Get addresses for the logged in user.
     *
     * @return Collection
     */
    private function getAddresses()
    {
        if (auth()->guest()) {
            return collect();
        }

        return auth()->user()->addresses->keyBy('id');
    }


    private function syncCustomerInfoToCart(Request $request)
    {
        $sessionId = session()->getId();
        $cartIds = [$sessionId . '_cart_items', $sessionId . '_cart_conditions'];

        // Extract customer info from request
        // Email and phone come from root level (form.customer_email, form.customer_phone)
        // Names come from shipping object (form.shipping.first_name, form.shipping.last_name)
        $data = [
            'customer_email' => $request->input('customer_email') ?? $request->input('billing.billing_email'),
            'customer_first_name' => $request->input('shipping.first_name') ?? $request->input('billing.first_name'),
            'customer_last_name' => $request->input('shipping.last_name') ?? $request->input('billing.last_name'),
            'customer_phone' => $request->input('customer_phone') ?? $request->input('shipping.phone') ?? $request->input('billing.phone'),
            'user_id' => auth()->id(),
        ];

        // Filter out null values to avoid overwriting with empty data
        $data = array_filter($data, function($value) {
            return !is_null($value) && $value !== '';
        });

        if (!empty($data)) {
            // Validate email before saving to avoid breaking mailer later
            if (!empty($data['customer_email'])) {
                $validator = \Illuminate\Support\Facades\Validator::make($data, [
                    'customer_email' => 'email',
                ]);
                
                if ($validator->fails()) {
                    \Log::warning('Invalid email format, not saving email to cart: ' . $data['customer_email']);
                    unset($data['customer_email']);
                }
            }

            if (!empty($data)) {
                foreach ($cartIds as $cartId) {
                    \Modules\Cart\Entities\Cart::whereIn('id', $cartIds)->update($data);
                }
            }
        }
    }
}

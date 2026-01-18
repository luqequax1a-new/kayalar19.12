<?php

namespace Modules\Account\Http\Controllers;

use Modules\Coupon\Entities\Coupon;
use Illuminate\Routing\Controller;

class AccountCouponsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::withoutGlobalScope('active')
            ->with('redeemedOrder')
            ->where('customer_id', auth()->id())
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->orderBy('is_active', 'desc')
            ->orderBy('end_date', 'asc')
            ->get();

        return view('storefront::public.account.coupons.index', compact('coupons'));
    }
}

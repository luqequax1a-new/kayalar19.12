<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\User\Entities\User;
use Modules\Order\Entities\Order;
use Modules\Review\Entities\Review;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\SearchTerm;
use Illuminate\Database\Eloquent\Collection;

class DashboardController
{
    /**
     * Display the dashboard with its widgets.
     *
     * @return Response
     */
    public function index()
    {
        return view('admin::dashboard.index', [
            'totalSales' => Order::totalSales(),
            'totalOrders' => Order::withoutCanceledOrders()->count(),
            'totalProducts' => Product::withoutGlobalScope('active')->count(),
            'totalCustomers' => User::totalCustomers(),
            'latestSearchTerms' => $this->getLatestSearchTerms(),
            'latestOrders' => $this->getLatestOrders(),
            'latestReviews' => $this->getLatestReviews(),
            'topCustomers' => $this->getTopCustomers(),
        ]);
    }


    private function getLatestSearchTerms()
    {
        return SearchTerm::latest('updated_at')->take(5)->get();
    }


    /**
     * Get latest five orders.
     *
     * @return Collection
     */
    private function getLatestOrders()
    {
        return Order::select([
            'id',
            'customer_first_name',
            'customer_last_name',
            'payment_method',
            'total',
            'status',
            'created_at',
        ])->latest()->take(15)->get();
    }


    /**
     * Get latest five reviews.
     *
     * @return Collection
     */
    private function getLatestReviews()
    {
        return Review::select('id', 'product_id', 'reviewer_name', 'rating')
            ->has('product')
            ->with('product:id')
            ->limit(5)
            ->get();
    }


    private function getTopCustomers()
    {
        return DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.customer_id')
            ->whereNotNull('orders.customer_id')
            ->whereNotIn('orders.status', [Order::CANCELED, Order::REFUNDED])
            ->groupBy('orders.customer_id', 'users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->orderByDesc(DB::raw('SUM(orders.total)'))
            ->limit(10)
            ->get([
                'users.id as id',
                'users.first_name as first_name',
                'users.last_name as last_name',
                'users.email as email',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('SUM(orders.total) as total_spent'),
            ]);
    }
}

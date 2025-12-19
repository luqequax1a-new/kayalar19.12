<?php

use Illuminate\Support\Facades\Route;

Route::get('orders', [
    'as' => 'admin.orders.index',
    'uses' => 'OrderController@index',
    'middleware' => 'can:admin.orders.index',
]);

Route::get('orders/{id}', [
    'as' => 'admin.orders.show',
    'uses' => 'OrderController@show',
    'middleware' => 'can:admin.orders.show',
]);

Route::put('orders/{id}', [
    'as' => 'admin.orders.update',
    'uses' => 'OrderController@update',
    'middleware' => 'can:admin.orders.edit',
]);

Route::get('orders/index/table', [
    'as' => 'admin.orders.table',
    'uses' => 'OrderController@table',
    'middleware' => 'can:admin.orders.index',
]);

Route::put('orders/{order}/status', [
    'as' => 'admin.orders.status.update',
    'uses' => 'OrderStatusController@update',
    'middleware' => 'can:admin.orders.edit',
]);

Route::post('orders/{order}/email', [
    'as' => 'admin.orders.email.store',
    'uses' => 'OrderEmailController@store',
    'middleware' => 'can:admin.orders.show',
]);

Route::get('orders/{order}/print', [
    'as' => 'admin.orders.print.show',
    'uses' => 'OrderPrintController@show',
    'middleware' => 'can:admin.orders.show',
]);

// Manual Orders module disabled

Route::group(['prefix' => 'cart-links', 'as' => 'admin.cart_links.'], function () {
    Route::get('create', [
        'as' => 'create',
        'uses' => 'CartLinkController@create',
        'middleware' => 'can:admin.cart_links.create',
    ]);

        Route::post('store', [
            'as' => 'store',
            'uses' => 'CartLinkController@store',
            'middleware' => 'can:admin.cart_links.create',
        ]);

        Route::post('orders/store', [
            'as' => 'orders.store',
            'uses' => 'CartLinkController@createOrder',
            'middleware' => 'can:admin.cart_links.create',
        ]);

        Route::post('preview', [
            'as' => 'preview',
            'uses' => 'CartLinkController@preview',
            'middleware' => 'can:admin.cart_links.create',
        ]);

        Route::get('locations/cities', [
            'as' => 'locations.cities',
            'uses' => 'CartLinkController@cities',
            'middleware' => 'can:admin.cart_links.create',
        ]);

        Route::get('locations/districts', [
            'as' => 'locations.districts',
            'uses' => 'CartLinkController@districts',
            'middleware' => 'can:admin.cart_links.create',
        ]);

    Route::get('products/search', [
        'as' => 'products.search',
        'uses' => 'ManualOrderController@searchProducts',
        'middleware' => 'can:admin.cart_links.create',
    ]);
    Route::get('customers/search', [
        'as' => 'customers.search',
        'uses' => 'ManualOrderController@searchCustomers',
        'middleware' => 'can:admin.cart_links.create',
    ]);

    Route::get('customers/{customer}/addresses', [
        'as' => 'customers.addresses',
        'uses' => 'CartLinkController@addresses',
        'middleware' => 'can:admin.cart_links.create',
    ]);
});

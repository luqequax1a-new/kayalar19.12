<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'can:admin.coupons.index'], function () {
    Route::get('cart-upsell-rules', [
        'as' => 'admin.cart_upsell_rules.index',
        'uses' => 'CartUpsellRuleController@index',
    ]);

    Route::get('cart-upsell-rules/create', [
        'as' => 'admin.cart_upsell_rules.create',
        'uses' => 'CartUpsellRuleController@create',
    ]);

    Route::post('cart-upsell-rules', [
        'as' => 'admin.cart_upsell_rules.store',
        'uses' => 'CartUpsellRuleController@store',
    ]);

    Route::get('cart-upsell-rules/{id}/edit', [
        'as' => 'admin.cart_upsell_rules.edit',
        'uses' => 'CartUpsellRuleController@edit',
    ]);

    Route::put('cart-upsell-rules/{id}', [
        'as' => 'admin.cart_upsell_rules.update',
        'uses' => 'CartUpsellRuleController@update',
    ]);

    Route::delete('cart-upsell-rules/{id}', [
        'as' => 'admin.cart_upsell_rules.destroy',
        'uses' => 'CartUpsellRuleController@destroy',
    ]);

    Route::get('abandoned-carts', [
        'as' => 'admin.abandoned_carts.index',
        'uses' => 'AbandonedCartController@index',
    ]);

    Route::get('abandoned-carts/{id}', [
        'as' => 'admin.abandoned_carts.show',
        'uses' => 'AbandonedCartController@show',
    ]);

    Route::post('abandoned-carts/{id}/send-reminder', [
        'as' => 'admin.abandoned_carts.send_reminder',
        'uses' => 'AbandonedCartController@sendReminder',
    ]);

    Route::post('abandoned-carts/clear', [
        'as' => 'admin.abandoned_carts.clear',
        'uses' => 'AbandonedCartController@clear',
    ]);

});

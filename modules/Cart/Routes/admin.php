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


});

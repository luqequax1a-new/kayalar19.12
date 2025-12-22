<?php

use Illuminate\Support\Facades\Route;

Route::get('checkout', 'CheckoutController@create')->name('checkout.create');
Route::post('checkout', 'CheckoutController@store')->name('checkout.store');
Route::post('checkout/update', 'CheckoutController@update')->name('checkout.update');

Route::any('checkout/{orderId}/complete', 'CheckoutCompleteController@store')
    ->name('checkout.complete.store')
    ->withoutMiddleware(\FleetCart\Http\Middleware\VerifyCsrfToken::class);
Route::get('checkout/complete', 'CheckoutCompleteController@show')->name('checkout.complete.show');

Route::get('payment-link/{orderId}', 'PaymentLinkController@show')
    ->name('checkout.payment_link.show')
    ->middleware('signed');

Route::any('checkout/{orderId}/payment-canceled', 'PaymentCanceledController@store')->name('checkout.payment_canceled.store')->withoutMiddleware(\FleetCart\Http\Middleware\VerifyCsrfToken::class);

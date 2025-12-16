<?php

use Illuminate\Support\Facades\Route;

Route::get('products', 'ProductController@index')
    ->middleware('listing.instrumentation')
    ->name('products.index');

Route::get('products/{slug}', 'ProductController@show')->name('products.show');

Route::get('products/{id}/related', 'ProductController@related')->name('products.related');
Route::get('products/{id}/upsell', 'ProductController@upsell')->name('products.upsell');

Route::post('products/{id}/price', 'ProductPriceController@show')->name('products.price.show');

Route::post('stock-notify', 'StockNotifyController@store')->name('stock.notify');

Route::get('suggestions', 'SuggestionController@index')->name('suggestions.index');

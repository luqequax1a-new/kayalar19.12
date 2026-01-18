<?php

use Illuminate\Support\Facades\Route;

$slug = setting('products_page_slug', 'products');

Route::get($slug, 'ProductController@index')
    ->middleware('listing.instrumentation')
    ->name('products.index');

// Legacy product URL - redirect to clean URL (preserve query string for variants)
Route::get($slug . '/{slug}', function($slug) {
    $query = request()->getQueryString();
    $url = '/' . $slug . ($query ? '?' . $query : '');
    return redirect($url, 301);
})->name('products.show');

Route::get($slug . '/{id}/related', 'ProductController@related')->name('products.related');
Route::get($slug . '/{id}/upsell', 'ProductController@upsell')->name('products.upsell');

Route::post($slug . '/{id}/price', 'ProductPriceController@show')->name('products.price.show');

Route::post('stock-notify', 'StockNotifyController@store')->name('stock.notify');

Route::get('suggestions', 'SuggestionController@index')->name('suggestions.index');

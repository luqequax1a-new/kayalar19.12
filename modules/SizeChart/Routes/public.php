<?php

use Illuminate\Support\Facades\Route;

Route::get('size-charts/product/{productId}', [
    'as' => 'size_charts.product.show',
    'uses' => 'SizeChartController@showForProduct',
]);

<?php

use Illuminate\Support\Facades\Route;

Route::get('size-charts', [
    'as' => 'admin.size_charts.index',
    'uses' => 'SizeChartController@index',
    'middleware' => 'can:admin.size_charts.index',
]);

Route::get('size-charts/create', [
    'as' => 'admin.size_charts.create',
    'uses' => 'SizeChartController@create',
    'middleware' => 'can:admin.size_charts.create',
]);

Route::post('size-charts', [
    'as' => 'admin.size_charts.store',
    'uses' => 'SizeChartController@store',
    'middleware' => 'can:admin.size_charts.create',
]);

Route::get('size-charts/{id}/edit', [
    'as' => 'admin.size_charts.edit',
    'uses' => 'SizeChartController@edit',
    'middleware' => 'can:admin.size_charts.edit',
]);

Route::put('size-charts/{id}', [
    'as' => 'admin.size_charts.update',
    'uses' => 'SizeChartController@update',
    'middleware' => 'can:admin.size_charts.edit',
]);

Route::delete('size-charts/{ids?}', [
    'as' => 'admin.size_charts.destroy',
    'uses' => 'SizeChartController@destroy',
    'middleware' => 'can:admin.size_charts.destroy',
]);

Route::get('size-charts/index/table', [
    'as' => 'admin.size_charts.table',
    'uses' => 'SizeChartController@table',
    'middleware' => 'can:admin.size_charts.index',
]);

Route::get('size-charts/products', [
    'as' => 'admin.size_charts.products',
    'uses' => 'SizeChartController@searchProducts',
    'middleware' => 'can:admin.size_charts.edit',
]);

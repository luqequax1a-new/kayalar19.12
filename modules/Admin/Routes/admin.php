<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'DashboardController@index')->name('admin.dashboard.index');

Route::get('dashboard/analytics', [
    'as' => 'admin.dashboard.analytics.index',
    'uses' => 'EnhancedDashboardAnalyticsController@index',
    'middleware' => 'can:admin.orders.index',
]);

Route::get('dashboard/cart-activity', [
    'as' => 'admin.dashboard.cart_activity.index',
    'uses' => 'CartActivityController@index',
    'middleware' => 'can:admin.orders.index',
]);

Route::get('/sales-analytics', [
    'as' => 'admin.sales_analytics.index',
    'uses' => 'SalesAnalyticsController@index',
    'middleware' => 'can:admin.orders.index',
]);

Route::resource('tag-badges', 'TagBadgeController')
    ->except(['show'])
    ->names('admin.tag_badges');

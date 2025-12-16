<?php

use Illuminate\Support\Facades\Route;

Route::get('storefront', [
    'as' => 'admin.storefront.settings.edit',
    'uses' => 'StorefrontController@edit',
    'middleware' => 'can:admin.storefront.edit',
]);

Route::put('storefront', [
    'as' => 'admin.storefront.settings.update',
    'uses' => 'StorefrontController@update',
    'middleware' => 'can:admin.storefront.edit',
]);

Route::put('storefront/home-page-sections/order', [
    'as' => 'admin.storefront.home_page_sections.order.update',
    'uses' => 'StorefrontController@updateHomePageSectionsOrder',
    'middleware' => 'can:admin.storefront.edit',
]);

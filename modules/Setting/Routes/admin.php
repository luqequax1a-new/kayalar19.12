<?php

use Illuminate\Support\Facades\Route;

Route::get('settings', [
    'as' => 'admin.settings.edit',
    'uses' => 'SettingController@edit',
    'middleware' => 'can:admin.settings.edit',
]);

Route::put('settings', [
    'as' => 'admin.settings.update',
    'uses' => 'SettingController@update',
    'middleware' => 'can:admin.settings.edit',
]);

Route::get('settings/review-campaign', [
    'as' => 'admin.settings.review_campaign',
    'middleware' => 'can:admin.settings.edit',
    'uses' => function () {
        return view('setting::admin.review_campaign.index');
    },
]);

Route::get('settings/whatsapp-module', [
    'as' => 'admin.settings.whatsapp_module',
    'middleware' => 'can:admin.settings.edit',
    'uses' => function () {
        return view('setting::admin.whatsapp_module.index');
    },
]);

Route::get('settings/customizations', [
    'as' => 'admin.settings.customizations',
    'middleware' => 'can:admin.settings.edit',
    'uses' => function () {
        return view('setting::admin.customizations.index');
    },
]);
Route::get('settings/abandoned-cart', [
    'as' => 'admin.settings.abandoned_cart',
    'middleware' => 'can:admin.settings.edit',
    'uses' => function () {
        return view('setting::admin.abandoned_cart.index');
    },
]);

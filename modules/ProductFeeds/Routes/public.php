<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'feeds'], function () {
    Route::get('google-merchant.xml', 'Public\\GoogleFeedController@index')->name('feeds.google_merchant');
    Route::get('meta-catalog.json', 'Public\\MetaFeedController@index')->name('feeds.meta_catalog');
    Route::get('trendyol.xml', 'Public\\TrendyolFeedController@index')->name('feeds.trendyol');
    Route::get('hepsiburada.xml', 'Public\\HepsiburadaFeedController@index')->name('feeds.hepsiburada');
    Route::get('pinterest.xml', 'Public\\PinterestFeedController@index')->name('feeds.pinterest');
    Route::get('tiktok.json', 'Public\\TikTokFeedController@index')->name('feeds.tiktok');
    Route::get('cron/{channel}', 'Public\\CronRefreshController@handle')->name('feeds.cron');
});

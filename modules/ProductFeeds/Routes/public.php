<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'feeds'], function () {
    Route::get('google-merchant.xml', 'Public\\GoogleFeedController@index')->name('feeds.google_merchant');
    Route::get('meta-catalog.json', 'Public\\MetaFeedController@index')->name('feeds.meta_catalog');
    Route::get('cron/{channel}', 'Public\\CronRefreshController@handle')->name('feeds.cron');
});

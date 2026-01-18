<?php

use Illuminate\Support\Facades\Route;

Route::get('products/{productId}/questions', 'ProductQuestionController@index')->name('products.questions.index');
Route::post('products/{productId}/questions', 'ProductQuestionController@store')->name('products.questions.store');

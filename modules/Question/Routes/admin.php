<?php

use Illuminate\Support\Facades\Route;

Route::get('questions', [
    'as' => 'admin.questions.index',
    'uses' => 'QuestionController@index',
    'middleware' => 'can:admin.questions.index',
]);

Route::get('questions/{id}/edit', [
    'as' => 'admin.questions.edit',
    'uses' => 'QuestionController@edit',
    'middleware' => 'can:admin.questions.edit',
]);

Route::put('questions/{id}', [
    'as' => 'admin.questions.update',
    'uses' => 'QuestionController@update',
    'middleware' => 'can:admin.questions.edit',
]);

Route::delete('questions/{ids?}', [
    'as' => 'admin.questions.destroy',
    'uses' => 'QuestionController@destroy',
    'middleware' => 'can:admin.questions.destroy',
]);

Route::get('questions/index/table', [
    'as' => 'admin.questions.table',
    'uses' => 'QuestionController@table',
    'middleware' => 'can:admin.questions.index',
]);

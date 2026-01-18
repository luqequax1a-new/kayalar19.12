<?php

use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::get('login', 'AuthController@getLogin')->name('login');
Route::post('login', 'AuthController@postLogin')->name('login.post');
Route::get('login/verify', 'AuthController@getVerify')->name('login.verify');
Route::post('login/verify', 'AuthController@postVerify')->name('login.verify.post');
Route::post('login/verify/resend', 'AuthController@postResendCode')->name('login.verify.resend');
Route::post('login/verify/resend/sms', 'AuthController@postResendCodeSms')->name('login.verify.resend.sms');

Route::get('login/{provider}', 'AuthController@redirectToProvider')->name('login.redirect');
Route::get('login/{provider}/callback', 'AuthController@handleProviderCallback')->name('login.callback');

Route::get('logout', 'AuthController@getLogout')->name('logout');

Route::get('register', 'AuthController@getRegister')->name('register');
Route::post('register', 'AuthController@postRegister')
    ->name('register.post')
    ->middleware(ProtectAgainstSpam::class);

Route::get('password/reset', 'AuthController@getReset')->name('reset');
Route::post('password/reset', 'AuthController@postReset')->name('reset.post');
Route::get('password/reset/{email}/{code}', 'AuthController@getResetComplete')->name('reset.complete');
Route::post('password/reset/{email}/{code}', 'AuthController@postResetComplete')->name('reset.complete.post');

<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\BkashPaymentController;
use Modules\Payment\Http\Controllers\PaytrPaymentController;

Route::post('/bkash/get-token', [BkashPaymentController::class, 'getToken'])
    ->name('bkash.get_token');

Route::get('/bkash/create-payment', [BkashPaymentController::class, 'createPayment'])
    ->name('bkash.create_payment');

Route::post('/bkash/execute-payment', [BkashPaymentController::class, 'executePayment'])
    ->name('bkash.execute_payment');

Route::get('/bkash/query-payment', [BkashPaymentController::class, 'queryPayment'])
    ->name('bkash.query_payment');

Route::post('/payment/paytr/callback', [PaytrPaymentController::class, 'callback'])
    ->name('payment.paytr.callback')
    ->withoutMiddleware(\FleetCart\Http\Middleware\VerifyCsrfToken::class);

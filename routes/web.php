<?php

use App\Http\Controllers\PaystackWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhooks/paystack', PaystackWebhookController::class)
    ->name('webhooks.paystack');

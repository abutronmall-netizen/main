<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('merchants/{merchant}/payments', [PaymentController::class, 'store']);
    Route::post('merchants/{merchant}/transactions/{transactionId}/refund', [RefundController::class, 'store']);
});

Route::post('merchants/{merchant}/webhooks/fnb', WebhookController::class);

<?php

use Illuminate\Support\Facades\Route;
use Inwebuz\UzumbankMerchant\Controllers\UzumbankMerchantController;
use Inwebuz\UzumbankMerchant\Middleware\BasicAuthMiddleware;

Route::group(['prefix' => 'uzumbank-merchant', 'middleware' => [BasicAuthMiddleware::class]], function () {
    Route::post('check', [UzumbankMerchantController::class, 'check']);
    Route::post('create', [UzumbankMerchantController::class, 'create']);
    Route::post('confirm', [UzumbankMerchantController::class, 'confirm']);
    Route::post('reverse', [UzumbankMerchantController::class, 'reverse']);
    Route::post('status', [UzumbankMerchantController::class, 'status']);
});

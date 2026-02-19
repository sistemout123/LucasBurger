<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// POS Core APIs (No auth for now, prepare for local-first testing)
Route::prefix('pos')->group(function () {
    Route::apiResource('tables', \App\Http\Controllers\Api\TableController::class)->only(['index', 'show']);
    Route::apiResource('orders', \App\Http\Controllers\Api\OrderController::class)->only(['index', 'store', 'show']);
    Route::post('orders/{order}/payments', [\App\Http\Controllers\Api\PaymentController::class, 'store']);
});

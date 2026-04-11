<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\StaffManagementController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('products', ProductController::class);

    Route::get('/stocks', [StockController::class, 'index']);
    Route::post('/stocks/in', [StockController::class, 'store']);
    Route::post('/stocks/out', [StockController::class, 'stockOut']);

    Route::get('/transactions', [TransactionController::class, 'index']);

    Route::get('/staffs', [StaffManagementController::class, 'index']);
    Route::post('/staffs', [StaffManagementController::class, 'store']);
    Route::get('/staffs/{staff}', [StaffManagementController::class, 'show']);
    Route::put('/staffs/{staff}', [StaffManagementController::class, 'update']);
    Route::delete('/staffs/{staff}', [StaffManagementController::class, 'destroy']);

    Route::post('/staffs/change-pin', [AuthController::class, 'changePin']);
});

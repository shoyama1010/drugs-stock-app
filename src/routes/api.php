<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\StaffManagementController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ShipmentController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('products', ProductController::class);

    Route::get('/stocks', [StockController::class, 'index']);
    Route::post('/stocks/in', [StockController::class, 'store']);
    Route::post('/stocks/out', [StockController::class, 'stockOut']);

    Route::get('/transactions', [TransactionController::class, 'index']);

    Route::get('/stores', [ShipmentController::class, 'stores']);
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::post('/shipments', [ShipmentController::class, 'store']);
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show']);
    Route::post('/shipments/{shipment}/confirm-out', [ShipmentController::class, 'confirmOut']);
    Route::get('/shipments/{shipment}/slip', [ShipmentController::class, 'slip']);
    Route::post('/shipments/{shipment}/dispatch', [ShipmentController::class, 'dispatch']);
    Route::post('/shipments/{shipment}/deliver', [ShipmentController::class, 'deliver']);

    Route::get('/staffs', [StaffManagementController::class, 'index']);
    Route::post('/staffs', [StaffManagementController::class, 'store']);
    Route::get('/staffs/{staff}', [StaffManagementController::class, 'show']);
    Route::put('/staffs/{staff}', [StaffManagementController::class, 'update']);
    Route::delete('/staffs/{staff}', [StaffManagementController::class, 'destroy']);

    Route::post('/staffs/change-pin', [AuthController::class, 'changePin']);
});

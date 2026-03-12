<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TransactionController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    Route::get('/products/create', [ProductController::class, 'create']);
    Route::post('/products', [ProductController::class, 'store']);

    Route::get('/stocks', [StockController::class, 'index']);
    Route::get('/stocks/in', [StockController::class, 'stockIn']);
    Route::get('/stocks/out', [StockController::class, 'stockOut']);

    Route::get('/transactions', [TransactionController::class, 'index']);
});

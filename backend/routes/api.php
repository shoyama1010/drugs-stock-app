<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AuthController;


Route::get('/test', function () {
    return response()->json([
        'message' => 'API OK'
    ]);
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
// admin
// Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'adminLogin']);
// staff
Route::post('/staff/login', [AuthController::class, 'staffLogin']); // 追加
// ログアウト（認証必要）
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

/*
|--------------------------------------------------------------------------
| 認証後
|--------------------------------------------------------------------------
*/

// Route::middleware('auth:sanctum')->group(function () {

/*
    商品管理
    */

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

/*
    在庫
    */

Route::get('/stocks', [StockController::class, 'index']);

Route::post('/stocks/in', [StockController::class, 'stockIn']);

Route::post('/stocks/out', [StockController::class, 'stockOut']);

/*
    履歴
    */

Route::get('/transactions', [TransactionController::class, 'index']);

Route::get('/transactions/export', [TransactionController::class, 'exportCsv']);

// });

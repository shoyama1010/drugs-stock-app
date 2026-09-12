<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        try {
            $transactions = Transaction::query()
                ->join('products', 'transactions.product_id', '=', 'products.id')
                ->leftJoin('locations', 'transactions.location_id', '=', 'locations.id')
                // 作業者情報を取得
                ->leftJoin(
                    'users',
                    'transactions.user_id',
                    '=',
                    'users.id'
                )

                ->select(
                    'transactions.id',
                    'transactions.type',
                    'transactions.quantity',
                    'transactions.note',
                    'transactions.created_at as transaction_date',
                    'products.name as product_name',
                    'products.sku',

                    'locations.zone as zone',
                    'locations.aisle as aisle',
                    'locations.shelf as shelf',

                    // 実際に操作したユーザー名
                    'users.name as staff_name'
                )
                ->orderBy('transactions.created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'date' => $item->transaction_date,
                        'productName' => $item->product_name,
                        'sku' => $item->sku,
                        'type' => $item->type === 'in' ? '入庫' : '出庫',
                        'quantity' => $item->quantity,
                        
                        // 'staff' => 'admin',
                        'staff' => $item->staff_name ?? '不明',

                        'reason' => $item->note,
                        'location' => $item->zone
                            ? "{$item->zone}-{$item->aisle}-{$item->shelf}"
                            : '-',
                    ];
                });

            return response()->json($transactions);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'transactions index failed',
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
}

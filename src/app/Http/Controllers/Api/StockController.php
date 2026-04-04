<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockLot;
use App\Models\Location;
use App\Models\StockLotLocation;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $stocks = DB::table('stock_lot_locations')
            ->join('stock_lots', 'stock_lot_locations.stock_lot_id', '=', 'stock_lots.id')
            ->join('products', 'stock_lots.product_id', '=', 'products.id')
            ->join('locations', 'stock_lot_locations.location_id', '=', 'locations.id')
            ->select(
                // 'products.code',
                'products.id as product_id',
                'products.name',
                'products.sku',
                'locations.aisle',   // ←必須
                'locations.shelf',
                DB::raw('SUM(stock_lot_locations.quantity_remaining) as total_stock')
            )
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'locations.aisle',   // ←必須
                'locations.shelf'
            )
           
            ->get();

        return response()->json($stocks);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

            // 🟢 ① 棚番号 → location取得
            $location = Location::whereRaw(
                "CONCAT(aisle, '-', shelf) = ?",
                [$request->shelf]
            )->first();

            if (!$location) {
                return response()->json([
                    'message' => '棚番号が存在しません'
                ], 422);
            }

            // ① ロット作成
            $lot = StockLot::create([
                'product_id' => $request->product_id,
                'lot_number' => $request->lot_number,
                'quantity_total' => $request->quantity,
                'received_at' => now(),
                'expiry_date' => $request->expiry_date,
            ]);

            // 🟢 ③ 在庫ロケーション登録
            StockLotLocation::create([
                'stock_lot_id' => $lot->id,
                'location_id' => $location->id, // ←ここが変換結果
                'quantity_initial' => $request->quantity,
                'quantity_remaining' => $request->quantity,
            ]);

            // ② 棚ごとに分割登録
            // foreach ($request->locations as $loc) {
            //     StockLotLocation::create([
            //         'stock_lot_id' => $lot->id,
            //         'location_id' => $loc['location_id'],
            //         'quantity_initial' => $loc['quantity'],
            //         'quantity_remaining' => $loc['quantity'],
            //     ]);
            // }

            return response()->json([
                'message' => '入庫完了'
            ]);
        });
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockLot;
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
                'products.code',
                'products.name',
                'stock_lots.lot_number',
                'locations.aisle',
                'locations.shelf',
                'locations.position',
                'stock_lot_locations.quantity_remaining'
            )
            ->where('stock_lot_locations.quantity_remaining', '>', 0)
            ->orderBy('products.code')
            ->get();

        return response()->json($stocks);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

            // ① ロット作成
            $lot = StockLot::create([
                'product_id' => $request->product_id,
                'lot_number' => $request->lot_number,
                'quantity_total' => $request->quantity,
                'received_at' => now(),
                'expiry_date' => $request->expiry_date,
            ]);

            // ② 棚ごとに分割登録
            foreach ($request->locations as $loc) {
                StockLotLocation::create([
                    'stock_lot_id' => $lot->id,
                    'location_id' => $loc['location_id'],
                    'quantity_initial' => $loc['quantity'],
                    'quantity_remaining' => $loc['quantity'],
                ]);
            }

            return response()->json([
                'message' => '入庫完了'
            ]);
        });
    }
}

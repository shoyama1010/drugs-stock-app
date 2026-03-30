<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
}

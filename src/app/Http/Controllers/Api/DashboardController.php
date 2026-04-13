<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockLotLocation;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_products' => Product::count(),

            'total_stock' => StockLotLocation::sum('quantity_remaining'),

            'today_in' => Transaction::where('type', 'in')
                ->whereDate('created_at', Carbon::today())
                ->sum('quantity'),

            'today_out' => Transaction::where('type', 'out')
                ->whereDate('created_at', Carbon::today())
                ->sum('quantity'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockLotLocation;

class StockController extends Controller
{
    public function index()
    {

        $stocks = StockLotLocation::with([
            'stockLot.product',
            'location'
        ])->get();

        return view('admin.stocks.index', compact('stocks'));

    }
}

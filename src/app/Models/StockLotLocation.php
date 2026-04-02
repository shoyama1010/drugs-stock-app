<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockLot;
use App\Models\Location;

class StockLotLocation extends Model
{
    protected $fillable = [
        'stock_lot_id',
        'location_id',
        'quantity_initial',
        'quantity_remaining',
    ];

    public function stockLot()
    {
        return $this->belongsTo(StockLot::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}

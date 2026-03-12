<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockLot;
use App\Models\Location;

class StockLotLocation extends Model
{

    public function stockLot()
    {
        return $this->belongsTo(StockLot::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}

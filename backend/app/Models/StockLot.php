<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLot extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function locations()
    {
        return $this->hasMany(StockLotLocation::class);
    }
}

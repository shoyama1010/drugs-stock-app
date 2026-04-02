<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLot extends Model
{
    protected $fillable = [
        'product_id',
        'lot_number',
        'quantity_total',
        'received_at',
        'expiry_date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function locations()
    {
        return $this->hasMany(StockLotLocation::class);
    }
}

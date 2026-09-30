<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentItemAllocation extends Model
{
    protected $fillable = [
        'shipment_item_id',
        'stock_lot_id',
        'location_id',
        'quantity',
    ];

    public function shipmentItem()
    {
        return $this->belongsTo(ShipmentItem::class);
    }

    public function stockLot()
    {
        return $this->belongsTo(StockLot::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}

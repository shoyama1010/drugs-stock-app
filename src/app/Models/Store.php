<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'store_code',
        'name',
        'address',
        'phone',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}

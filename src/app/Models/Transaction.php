<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'stock_lot_id',
        'user_id',
        'type',
        'quantity',
        'location_id',
        'store_id',
        'note',
    ];
}


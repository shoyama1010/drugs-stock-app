<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Product extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sku',
        'category_id',
        'unit_price',
        'min_stock',
        'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockLots()
    {
        return $this->hasMany(StockLot::class);
    }
}

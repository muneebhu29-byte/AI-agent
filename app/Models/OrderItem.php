<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id', 'product_name_snapshot',
        'sku_snapshot', 'unit_price', 'quantity', 'line_total',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

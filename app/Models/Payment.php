<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'provider', 'provider_payment_id', 'provider_checkout_session_id',
        'amount', 'currency', 'status', 'paid_at', 'raw_payload',
    ];

    protected $casts = ['raw_payload' => 'array', 'paid_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

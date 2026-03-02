<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'type', 'first_name', 'last_name', 'line1', 'line2',
        'city', 'state', 'postal_code', 'country_code', 'phone', 'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

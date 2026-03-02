<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }
}

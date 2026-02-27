<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'active',
    ];

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function owner()
{
    return $this->belongsTo(\App\Models\User::class, 'user_id');
}

public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}
}
    
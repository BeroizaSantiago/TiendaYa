<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'token',
        'store_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

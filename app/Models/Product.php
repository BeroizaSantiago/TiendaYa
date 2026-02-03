<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'description',
        'price',
        'stock',
        'active',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}

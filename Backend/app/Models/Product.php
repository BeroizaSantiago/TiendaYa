<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'active',
    ];
    public function store()
    {
        return $this->belongsTo(\App\Models\Store::class);
    }
        public function category()
    {
        return $this->belongsTo(Category::class);
    }

        public function attributeStocks()
    {
        return $this->hasMany(ProductAttributeStock::class);
    }
}

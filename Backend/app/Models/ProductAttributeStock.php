<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeStock extends Model
{
    protected $table = 'product_attribute_stock';

    protected $fillable = [
        'product_id',
        'attribute_value_id',
        'stock'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }
    
}
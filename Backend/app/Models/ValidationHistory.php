<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationHistory extends Model
{
    protected $fillable = [
        'user_id',
        'validation_status',
        'observations',
        'validated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'recipient_name',
        'address',
        'city',
        'state',
        'postal_code',
        'phone',
        'status',
        'tracking_number',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function trackingEvents()
    {
        return $this->hasMany(ShipmentTrackingEvent::class)
                    ->orderBy('created_at');
    }

}



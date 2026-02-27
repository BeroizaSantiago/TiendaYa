<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\DeliveryNote;
use App\Http\Controllers\Controller;


class DeliveryNoteController extends Controller
{
    public function issue(int $orderId)
{
    $order = Order::with('deliveryNote')->findOrFail($orderId);

    if ($order->deliveryNote) {
        return response()->json([
            'message' => 'Delivery note already exists'
        ], 422);
    }

    $nextNumber = DeliveryNote::max('delivery_number') + 1;

    $note = DeliveryNote::create([
        'order_id'       => $order->id,
        'user_id'        => 1,
        'delivery_number'=> $nextNumber,
        'status'         => 'issued',
        'issued_at'      => now(),
    ]);

    return response()->json($note, 201);
}
}

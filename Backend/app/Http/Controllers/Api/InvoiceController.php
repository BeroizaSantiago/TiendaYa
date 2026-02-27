<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Invoice; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function issue(int $orderId)
{
    $order = Order::with('invoice')->findOrFail($orderId);

    if ($order->invoice) {
        return response()->json([
            'message' => 'Invoice already exists'
        ], 422);
    }

    return DB::transaction(function () use ($order) {

        $nextNumber = Invoice::max('invoice_number') + 1;

        $invoice = Invoice::create([
            'order_id'      => $order->id,
            'user_id'       => 1, 
            'invoice_type'  => 'B',
            'invoice_number'=> $nextNumber,
            'total'         => $order->total,
            'status'        => 'issued',
            'issued_at'     => now(),
        ]);

        return response()->json($invoice, 201);
    });
}
}

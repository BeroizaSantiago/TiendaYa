<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\OrderStatusHistory;
use App\Models\Shipment;

class CheckoutController extends Controller
{
    public function checkout(Request $request, int $storeId): JsonResponse
{
    $token = $request->query('token');

    $cart = Cart::where('store_id', $storeId)
        ->where('token', $token)
        ->with('items.product')
        ->firstOrFail();

    if ($cart->items->isEmpty()) {
        return response()->json([
            'message' => 'Cart is empty',
        ], 422);
    }

    return DB::transaction(function () use ($cart, $storeId) {

        $total = 0;

        //  Bloquear productos
        foreach ($cart->items as $item) {

            $product = Product::where('id', $item->product_id)
                ->lockForUpdate()
                ->first();

            if ($product->stock < $item->quantity) {
                abort(422, "Not enough stock for {$product->name}");
            }

            $product->decrement('stock', $item->quantity);

            $total += $item->price * $item->quantity;
        }

        // Crear orden
        $order = Order::create([
            'store_id' => $storeId,
            'total'    => $total,
            'status'   => 'pending',
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'recipient_name' => $request->recipient_name ?? '',
            'address' => $request->address ?? '',
            'city' => $request->city ?? '',
            'state' => $request->state ?? '',
            'postal_code' => $request->postal_code ?? '',
            'phone' => $request->phone ?? '',
            'status' => 'pending',
        ]);


        OrderStatusHistory::create([
            'order_id' => $order->id,
            'old_status' => null,
            'new_status' => 'pending',
        ]);

        // Crear items
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item->product_id,
                'name'       => $item->product->name,
                'price'      => $item->price,
                'quantity'   => $item->quantity,
                'subtotal'   => $item->price * $item->quantity,
            ]);
        }

        // Vaciar carrito
        $cart->items()->delete();

        return response()->json([
            'message'  => 'Order created',
            'order_id' => $order->id,
            'total'    => $order->total,
        ], 201);
    });
}


    public function updateStatus(Request $request, int $orderId): JsonResponse
{
    $data = $request->validate([
        'status' => ['required', 'string'],
    ]);

    $order = Order::findOrFail($orderId);

    $allowed = [
        Order::STATUS_PENDING,
        Order::STATUS_PAID,
        Order::STATUS_SHIPPED,
        Order::STATUS_COMPLETED,
        Order::STATUS_CANCELLED,
    ];

    if (!in_array($data['status'], $allowed)) {
        return response()->json([
            'message' => 'Invalid status',
        ], 422);
    }

    $order->status = $data['status'];
    $order->save();

    return response()->json([
        'message' => 'Status updated',
        'order_id' => $order->id,
        'status' => $order->status,
    ]);
}

}

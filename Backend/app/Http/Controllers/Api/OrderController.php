<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\OrderStatusHistory;


class OrderController extends Controller
{
    // Historial de órdenes de una tienda
    public function index(int $storeId): JsonResponse
    {
        $orders = Order::where('store_id', $storeId)
            ->with('items')
            ->latest()
            ->get();

        return response()->json([
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'name' => $item->name,
                            'price' => (float) $item->price,
                            'quantity' => $item->quantity,
                            'subtotal' => (float) $item->subtotal,
                        ];
                    }),
                ];
            }),
        ]);
    }

    // Cancelar orden
    public function cancel(int $storeId, int $orderId): JsonResponse
    {
        $order = Order::where('store_id', $storeId)
            ->with('items')
            ->findOrFail($orderId);

        // solo permitir cancelar si no está finalizada
        if ($order->status === 'completed') {
            return response()->json([
                'message' => 'Completed orders cannot be cancelled',
            ], 422);
        }

        // devolver stock
        foreach ($order->items as $item) {
            $item->product()->increment('stock', $item->quantity);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'message' => 'Order cancelled',
        ]);
    }
    // Actualizar estado de la orden
    public function updateStatus(Request $request, int $storeId, int $orderId)
    {
        $newStatus = $request->input('status');

        $allowedStatuses = [
            'pending',
            'paid',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
        ];

        if (!in_array($newStatus, $allowedStatuses)) {
            return response()->json([
                'message' => 'Invalid status',
            ], 422);
        }

        $order = Order::where('store_id', $storeId)
            ->findOrFail($orderId);

        $transitions = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['processing', 'cancelled'],
            'processing' => ['shipped'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $transitions[$order->status])) {
            return response()->json([
                'message' => 'Invalid status transition',
                'current_status' => $order->status,
            ], 422);
        }

        $oldStatus = $order->status;

        $order->status = $newStatus;
        $order->save();

        // sincronizar shipment si existe
        if ($order->shipment) {
            if ($newStatus === 'shipped') {
                $order->shipment->status = 'shipped';
                $order->shipment->save();
            }

            if ($newStatus === 'delivered') {
                $order->shipment->status = 'delivered';
                $order->shipment->save();
            }
        }


        OrderStatusHistory::create([
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return response()->json([
            'message' => 'Status updated',
            'status' => $order->status,
        ]);
    }

    public function show($storeId, $orderId): JsonResponse
    {
        $order = \App\Models\Order::where('store_id', $storeId)
            ->with('items')
            ->findOrFail($orderId);

        return response()->json([
            'order' => $order
        ]);
    }

    public function history(int $storeId, int $orderId): JsonResponse
    {
        $order = Order::where('store_id', $storeId)
            ->with('statusHistory')
            ->findOrFail($orderId);

        return response()->json([
            'order_id' => $order->id,
            'history' => $order->statusHistory->map(function ($h) {
                return [
                    'from' => $h->old_status,
                    'to' => $h->new_status,
                    'changed_at' => $h->created_at,
                ];
            }),
        ]);
    }


    public function shipment($storeId, $orderId)
    {
        $order = Order::where('store_id', $storeId)
            ->with('shipment.trackingEvents')
            ->findOrFail($orderId);

        if (!$order->shipment) {
            return response()->json([
                'message' => 'Shipment not found'
            ], 404);
        }

        return response()->json($order->shipment);
    }

    public function tracking($storeId, $orderId)
    {
        $order = Order::where('store_id', $storeId)
            ->with('shipment.trackingEvents')
            ->findOrFail($orderId);

        return response()->json($order->shipment->trackingEvents);
    }
}

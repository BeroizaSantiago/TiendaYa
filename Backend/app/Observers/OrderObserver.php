<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\DeliveryNote;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order)
    {
        if (!$order->wasChanged('status')) {
            return;
        }

        $order->load(['store.users', 'store.plan']);

        $store = $order->store;

        if (!$store) {
            return;
        }

        // Obtener el owner de la tienda (role = owner)
        $owner = $store->users()
            ->wherePivot('role', 'owner')
            ->first();

        if (!$owner) {
            return;
        }

        $userId = $owner->id;

        $newStatus = $order->status;

        // Plan ahora pertenece a la store
        $commissionRate = $store->plan?->commission_rate ?? 0;
        $commissionAmount = $order->total * $commissionRate;

        /*
    |--------------------------------------------------------------------------
    | INVOICE
    |--------------------------------------------------------------------------
    */
        if ($newStatus === 'paid' && !$order->invoice()->exists()) {

            $nextNumber = (Invoice::max('invoice_number') ?? 0) + 1;

            Invoice::create([
                'order_id'         => $order->id,
                'user_id'          => $userId,
                'invoice_type'     => 'B',
                'invoice_number'   => $nextNumber,
                'total'            => $order->total,
                'status'           => 'issued',
                'commission_amount' => $commissionAmount,
                'issued_at'        => now(),
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | DELIVERY NOTE
    |--------------------------------------------------------------------------
    */
        if ($newStatus === 'shipped' && !$order->deliveryNote()->exists()) {

            $nextNumber = (DeliveryNote::max('delivery_number') ?? 0) + 1;

            DeliveryNote::create([
                'order_id'        => $order->id,
                'user_id'         => $userId,
                'delivery_number' => $nextNumber,
                'status'          => 'issued',
                'issued_at'       => now(),
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | SHIPMENT TRACKING
    |--------------------------------------------------------------------------
    */
        if ($newStatus === 'shipped') {

            $order->load('shipment');
            $shipment = $order->shipment;

            if ($shipment) {

                if (!$shipment->tracking_number) {
                    $shipment->tracking_number = 'TRK-' . strtoupper(uniqid());
                }

                $shipment->status = 'shipped';
                $shipment->save();

                $shipment->trackingEvents()->create([
                    'status'      => 'shipped',
                    'description' => 'Shipment dispatched from warehouse',
                    'location'    => 'Main Warehouse'
                ]);
            }
        }

        if ($newStatus === 'delivered') {

            $order->load('shipment');
            $shipment = $order->shipment;

            if ($shipment) {

                $shipment->status = 'delivered';
                $shipment->save();

                $shipment->trackingEvents()->create([
                    'status'      => 'delivered',
                    'description' => 'Package delivered to recipient',
                    'location'    => $shipment->city
                ]);
            }
        }
    }
    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}

<?php

namespace App\Actions;

use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class SelectShipping
{
    public function execute(Order $order, array $option): Shipment
    {
        return DB::transaction(function () use ($order, $option): Shipment {
            $shipment = Shipment::updateOrCreate(['order_id' => $order->id], ['provider' => 'melhor_envio', 'service_id' => $option['id'], 'carrier' => $option['carrier'], 'price' => $option['price'], 'status' => ShipmentStatus::QUOTED]);
            $order->update(['shipping_total' => $shipment->price, 'total' => round((float) $order->subtotal + (float) $shipment->price, 2)]);

            return $shipment;
        });
    }
}

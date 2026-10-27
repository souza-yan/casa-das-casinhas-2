<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Services\Shipping\SuperFreteService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncSuperFreteTracking implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 6;

    public int $uniqueFor = 7200;

    public array $backoff = [60, 300, 900, 1800, 3600];

    public function __construct(public int $shipmentId) {}

    public function uniqueId(): string
    {
        return (string) $this->shipmentId;
    }

    public function handle(SuperFreteService $shipping): void
    {
        $shipment = Shipment::find($this->shipmentId);

        if (! $shipment || filled($shipment->tracking_code) || blank($shipment->provider_shipment_id)) {
            return;
        }

        $trackingCode = $shipping->syncTracking($shipment);

        if (blank($trackingCode) && $this->attempts() < $this->tries) {
            $delay = $this->backoff[min($this->attempts() - 1, count($this->backoff) - 1)];
            $this->release($delay);
        }
    }
}

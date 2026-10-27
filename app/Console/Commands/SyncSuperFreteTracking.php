<?php

namespace App\Console\Commands;

use App\Enums\ShipmentStatus;
use App\Jobs\SyncSuperFreteTracking as SyncSuperFreteTrackingJob;
use App\Models\Shipment;
use Illuminate\Console\Command;

class SyncSuperFreteTracking extends Command
{
    protected $signature = 'shipping:sync-tracking';

    protected $description = 'Agenda a sincronização de códigos de rastreio pendentes da SuperFrete';

    public function handle(): int
    {
        $queued = 0;

        Shipment::query()
            ->where('provider', 'superfrete')
            ->where('status', ShipmentStatus::LABEL_PURCHASED)
            ->whereNotNull('provider_shipment_id')
            ->whereNull('tracking_code')
            ->chunkById(100, function ($shipments) use (&$queued): void {
                foreach ($shipments as $shipment) {
                    SyncSuperFreteTrackingJob::dispatch($shipment->id);
                    $queued++;
                }
            });

        $this->info("{$queued} sincronização(ões) de rastreio agendada(s).");

        return self::SUCCESS;
    }
}

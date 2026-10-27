<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Models\WebhookEvent;
use App\Services\Shipping\SuperFreteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ProcessSuperFreteWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public array $backoff = [60, 300, 900, 1800];

    public function __construct(public WebhookEvent $webhookEvent) {}

    public function handle(SuperFreteService $shipping): void
    {
        DB::transaction(function () use ($shipping): void {
            $event = WebhookEvent::lockForUpdate()->findOrFail($this->webhookEvent->id);
            if ($event->status === 'processed') {
                return;
            }

            $shipment = Shipment::where('provider', 'superfrete')
                ->where('provider_shipment_id', data_get($event->payload, 'data.id'))
                ->lockForUpdate()
                ->firstOrFail();
            $shipping->applyProviderData($shipment, data_get($event->payload, 'data', []));
            $event->update(['status' => 'processed', 'processed_at' => now()]);
        });
    }
}

<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = ['order_id', 'provider', 'provider_shipment_id', 'service_id', 'carrier', 'price', 'quote_payload', 'tracking_code', 'label_url', 'tracking_url', 'provider_status', 'tracking_synced_at', 'posted_at', 'delivered_at', 'status'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quote_payload' => 'array',
            'tracking_synced_at' => 'datetime',
            'posted_at' => 'datetime',
            'delivered_at' => 'datetime',
            'status' => ShipmentStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

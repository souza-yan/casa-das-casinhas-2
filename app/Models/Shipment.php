<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = ['order_id', 'provider', 'provider_shipment_id', 'service_id', 'carrier', 'price', 'quote_payload', 'tracking_code', 'status'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'quote_payload' => 'array', 'status' => ShipmentStatus::class];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderNotification extends Model
{
    protected $fillable = ['order_id', 'type', 'channel', 'recipient', 'provider_message_id', 'status', 'attempts', 'last_error', 'sent_at', 'delivered_at'];

    protected function casts(): array
    {
        return ['status' => NotificationStatus::class, 'attempts' => 'integer', 'sent_at' => 'datetime', 'delivered_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

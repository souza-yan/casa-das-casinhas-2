<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'customer_name', 'customer_email', 'customer_phone', 'subtotal', 'shipping_total', 'total', 'status', 'paid_at'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'shipping_total' => 'decimal:2', 'total' => 'decimal:2', 'status' => OrderStatus::class, 'paid_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(OrderAddress::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(OrderNotification::class);
    }

    public function transitionTo(OrderStatus $status): void
    {
        $this->status->assertCanTransitionTo($status);
        $this->update(['status' => $status]);
    }
}

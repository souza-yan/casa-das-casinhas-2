<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\SaleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'asaas_customer_id',
        'checkout_token',
        'guest_access_token_hash',
        'guest_access_expires_at',
        'subtotal',
        'shipping_total',
        'total',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => OrderStatus::class,
            'paid_at' => 'datetime',
            'guest_access_expires_at' => 'datetime',
        ];
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
        DB::transaction(function () use ($status): void {
            $order = self::lockForUpdate()->findOrFail($this->id);
            $order->status->assertCanTransitionTo($status);
            throw_if($order->status === OrderStatus::PAID && $status === OrderStatus::READY_TO_SHIP && $order->productionDays() > 0, \DomainException::class, 'Este pedido ainda precisa passar pela produção.');
            if ($status === OrderStatus::CANCELLED) {
                $quantities = $order->items()->where('sale_type', SaleType::STOCK->value)->get()
                    ->groupBy('product_id')->map(fn ($items) => $items->sum('quantity'));
                foreach (Product::withTrashed()->whereIn('id', $quantities->keys())->orderBy('id')->lockForUpdate()->get() as $product) {
                    $product->increment('stock_quantity', $quantities[$product->id]);
                }
            }
            $order->update(['status' => $status]);
            $this->setRawAttributes($order->getAttributes(), true);
        });
    }

    public function productionDays(): int
    {
        return (int) $this->items->max('production_days');
    }

    public function canTransitionTo(OrderStatus $status): bool
    {
        return $this->status->canTransitionTo($status)
            && ! ($this->status === OrderStatus::PAID && $status === OrderStatus::READY_TO_SHIP && $this->productionDays() > 0);
    }

    public function requiresPersonalizationApproval(): bool
    {
        return $this->items()->whereNotNull('personalization_description')->where('personalization_description', '!=', '')->exists();
    }
}

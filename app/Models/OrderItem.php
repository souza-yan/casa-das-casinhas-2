<?php

namespace App\Models;

use App\Enums\SaleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['product_id', 'product_name', 'quantity', 'unit_price', 'subtotal', 'personalization_description', 'reference_paths', 'production_days', 'sale_type'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'reference_paths' => 'array',
            'production_days' => 'integer',
            'sale_type' => SaleType::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}

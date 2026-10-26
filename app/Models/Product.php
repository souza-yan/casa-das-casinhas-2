<?php

namespace App\Models;

use App\Enums\SaleType;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    public const DEFAULT_WEIGHT_KG = 1.000;

    public const PRODUCTION_DAYS = 15;

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->isStockProduct()) {
                $product->is_customizable = false;
            }
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'image_path',
        'weight_kg',
        'height_cm',
        'width_cm',
        'length_cm',
        'production_days',
        'is_customizable',
        'is_active',
        'sale_type',
        'stock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'weight_kg' => 'decimal:3',
            'height_cm' => 'decimal:2',
            'width_cm' => 'decimal:2',
            'length_cm' => 'decimal:2',
            'production_days' => 'integer',
            'is_customizable' => 'boolean',
            'is_active' => 'boolean',
            'sale_type' => SaleType::class,
            'stock_quantity' => 'integer',
        ];
    }

    protected function productionDays(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->isStockProduct() ? 0 : self::PRODUCTION_DAYS,
            set: fn (): int => self::PRODUCTION_DAYS,
        );
    }

    protected function isCustomizable(): Attribute
    {
        return Attribute::make(
            get: fn ($value): bool => ! $this->isStockProduct() && (bool) $value,
        );
    }

    public function isStockProduct(): bool
    {
        return $this->sale_type === SaleType::STOCK;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && (! $this->isStockProduct() || $this->stock_quantity > 0);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return str_starts_with($this->image_path, 'images/')
            ? asset($this->image_path)
            : '/storage/'.ltrim($this->image_path, '/');
    }
}

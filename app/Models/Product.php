<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    public const DEFAULT_WEIGHT_KG = 1.000;

    public const PRODUCTION_DAYS = 15;

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
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    protected function productionDays(): Attribute
    {
        return Attribute::make(
            get: fn (): int => self::PRODUCTION_DAYS,
            set: fn (): int => self::PRODUCTION_DAYS,
        );
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return str_starts_with($this->image_path, 'images/')
            ? asset($this->image_path)
            : Storage::disk('public')->url($this->image_path);
    }
}

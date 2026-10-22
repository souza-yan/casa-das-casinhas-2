<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    protected $fillable = ['zip_code', 'street', 'number', 'complement', 'district', 'city', 'state'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

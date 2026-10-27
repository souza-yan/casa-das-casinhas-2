<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    protected $fillable = ['zip_code', 'street', 'number', 'complement', 'district', 'city', 'state', 'document'];

    public function getDocumentMaskedAttribute(): ?string
    {
        $document = preg_replace('/\D/', '', (string) $this->document);

        return match (strlen($document)) {
            11 => '***.'.substr($document, 3, 3).'.'.substr($document, 6, 3).'-**',
            14 => '**.***.***/'.substr($document, 8, 4).'-**',
            default => null,
        };
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

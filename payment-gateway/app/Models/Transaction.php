<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'merchant_id',
        'fnb_payment_id',
        'merchant_reference',
        'amount',
        'currency',
        'status',
        'captured_at',
        'refunded_at',
        'metadata',
        'raw_response',
    ];

    protected $casts = [
        'metadata' => 'array',
        'raw_response' => 'array',
        'captured_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $transaction) {
            if (!$transaction->getKey()) {
                $transaction->setAttribute($transaction->getKeyName(), (string) Str::uuid());
            }
        });
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Merchant extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'contact_email',
        'statement_email',
        'webhook_url',
        'status',
        'settlement_schedule',
    ];

    protected $casts = [
        'status' => 'string',
        'settlement_schedule' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $merchant) {
            if (!$merchant->getKey()) {
                $merchant->setAttribute($merchant->getKeyName(), (string) Str::uuid());
            }
        });
    }

    public function apiCredentials(): HasMany
    {
        return $this->hasMany(MerchantApiCredential::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}

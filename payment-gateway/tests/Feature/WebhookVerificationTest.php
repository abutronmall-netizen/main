<?php

use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('rejects invalid webhooks', function () {
    $merchant = Merchant::factory()->create();
    config(['fnb.webhook_secret' => 'whsec_test']);

    postJson("/api/merchants/{$merchant->id}/webhooks/fnb", [])
        ->assertStatus(403);
});

it('accepts valid webhooks and updates transaction', function () {
    $merchant = Merchant::factory()->create();
    config(['fnb.webhook_secret' => 'whsec_test']);

    $payload = [
        'data' => [
            'id' => 'fnb_'.Str::uuid(),
            'merchant_reference' => 'order-001',
            'amount' => 5000,
            'currency' => 'ZAR',
            'status' => 'captured',
        ],
    ];

    $json = json_encode($payload);
    $timestamp = (string) now()->timestamp;
    $secret = config('fnb.webhook_secret');
    $signature = hash_hmac('sha256', $timestamp.'.'.$json, $secret);

    postJson("/api/merchants/{$merchant->id}/webhooks/fnb", $payload, [
        'x-fnb-signature' => $signature,
        'x-fnb-timestamp' => $timestamp,
    ])->assertOk();

    expect(Transaction::where('merchant_id', $merchant->id)->count())->toBe(1);
});

<?php

use App\Models\Merchant;
use App\Models\Transaction;
use App\Services\Fnb\FnbClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

afterEach(function () {
    Mockery::close();
});

it('creates a payment and stores the transaction', function () {
    $merchant = Merchant::factory()->create();
    $user = \App\Models\User::factory()->create();
    Permission::findOrCreate('payments.create');
    $user->givePermissionTo('payments.create');

    $client = Mockery::mock(FnbClient::class);
    $this->app->instance(FnbClient::class, $client);

    $client->shouldReceive('createPayment')->once()->andReturn([
        'id' => 'fnb_'.Str::uuid(),
        'status' => 'authorized',
    ]);

    actingAs($user);

    $payload = [
        'merchant_reference' => 'order-123',
        'amount' => 5000,
        'currency' => 'ZAR',
        'description' => 'Test payment',
        'customer' => ['email' => 'customer@example.com'],
    ];

    postJson("/api/merchants/{$merchant->id}/payments", $payload)
        ->assertCreated()
        ->assertJsonPath('transaction.merchant_reference', 'order-123');

    expect(Transaction::count())->toBe(1);
});

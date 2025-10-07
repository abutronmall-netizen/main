<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\MerchantApiCredential;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        $merchant = Merchant::factory()->create([
            'name' => 'Acme Online',
            'slug' => 'acme-online',
            'contact_email' => 'ops@acme.test',
            'statement_email' => config('mail.from.address', 'finance@example.com'),
            'webhook_url' => config('app.url').'/api/merchants/acme-online/webhooks/fnb',
            'status' => 'active',
            'settlement_schedule' => [
                'frequency' => 'daily',
                'cutoff_hour' => 17,
            ],
        ]);

        MerchantApiCredential::create([
            'id' => (string) Str::uuid(),
            'merchant_id' => $merchant->id,
            'public_key' => 'pub_'.Str::random(32),
            'secret' => encrypt(Str::random(64)),
            'permissions' => ['payments.create', 'payments.refund'],
        ]);

        Transaction::factory(5)->create([
            'merchant_id' => $merchant->id,
        ]);
    }
}

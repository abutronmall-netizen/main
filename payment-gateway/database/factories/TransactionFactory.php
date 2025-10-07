<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'merchant_id' => Merchant::factory(),
            'fnb_payment_id' => 'fnb_'.$this->faker->uuid(),
            'merchant_reference' => 'order-'.$this->faker->unique()->numerify('#####'),
            'amount' => $this->faker->numberBetween(1000, 100000),
            'currency' => 'ZAR',
            'status' => $this->faker->randomElement(['pending', 'authorized', 'captured', 'refunded']),
            'metadata' => [
                'channel' => $this->faker->randomElement(['card', 'eft', 'qr']),
            ],
            'raw_response' => [],
        ];
    }
}

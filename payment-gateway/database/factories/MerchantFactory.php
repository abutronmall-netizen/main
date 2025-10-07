<?php

namespace Database\Factories;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MerchantFactory extends Factory
{
    protected $model = Merchant::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'id' => (string) Str::uuid(),
            'name' => $name,
            'slug' => Str::slug($name.'-'.$this->faker->unique()->randomNumber()),
            'contact_email' => $this->faker->companyEmail(),
            'statement_email' => $this->faker->safeEmail(),
            'webhook_url' => $this->faker->url(),
            'status' => 'active',
            'settlement_schedule' => [
                'frequency' => 'daily',
                'cutoff_hour' => 16,
            ],
        ];
    }
}

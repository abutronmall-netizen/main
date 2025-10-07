<?php

namespace App\Services\Fnb\Dto;

use Illuminate\Support\Arr;

class PaymentRequestData
{
    public function __construct(
        public readonly string $merchantReference,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $description,
        public readonly array $customer,
        public readonly array $metadata = [],
        public readonly ?string $paymentMethodToken = null,
        public readonly ?string $callbackUrl = null,
        public readonly bool $capture = true,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            merchantReference: Arr::get($data, 'merchant_reference'),
            amount: (int) Arr::get($data, 'amount'),
            currency: Arr::get($data, 'currency'),
            description: Arr::get($data, 'description'),
            customer: Arr::get($data, 'customer', []),
            metadata: Arr::get($data, 'metadata', []),
            paymentMethodToken: Arr::get($data, 'payment_method_token'),
            callbackUrl: Arr::get($data, 'callback_url'),
            capture: (bool) Arr::get($data, 'capture', true),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'merchant_reference' => $this->merchantReference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'customer' => $this->customer,
            'metadata' => $this->metadata,
            'payment_method_token' => $this->paymentMethodToken,
            'callback_url' => $this->callbackUrl,
            'capture' => $this->capture,
        ], fn ($value) => $value !== null);
    }
}

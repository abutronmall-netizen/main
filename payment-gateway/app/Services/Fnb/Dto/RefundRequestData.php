<?php

namespace App\Services\Fnb\Dto;

use Illuminate\Support\Arr;

class RefundRequestData
{
    public function __construct(
        public readonly string $paymentId,
        public readonly int $amount,
        public readonly string $reason,
        public readonly array $metadata = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            paymentId: Arr::get($data, 'payment_id'),
            amount: (int) Arr::get($data, 'amount'),
            reason: Arr::get($data, 'reason'),
            metadata: Arr::get($data, 'metadata', []),
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
        ];
    }
}

<?php

namespace App\Services\Fnb;

use App\Services\Fnb\Dto\PaymentRequestData;
use App\Services\Fnb\Dto\RefundRequestData;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;

class FnbClient
{
    public function __construct(
        private readonly HttpClientFactory $httpFactory,
        private readonly string $webhookSecret,
        private readonly string $verificationStrategy = 'strict',
    ) {
    }

    public function createPayment(PaymentRequestData $data): array
    {
        $client = $this->httpFactory->makeWithToken();

        $payload = $data->toArray();
        $response = $client->post('payments', [
            'json' => $payload,
            'headers' => $this->requestHeaders(),
        ]);

        return $this->json($response);
    }

    public function capturePayment(string $paymentId, array $metadata = []): array
    {
        $client = $this->httpFactory->makeWithToken();

        $response = $client->post("payments/{$paymentId}/capture", [
            'json' => ['metadata' => $metadata],
            'headers' => $this->requestHeaders(),
        ]);

        return $this->json($response);
    }

    public function refundPayment(RefundRequestData $data): array
    {
        $client = $this->httpFactory->makeWithToken();

        $response = $client->post("payments/{$data->paymentId}/refunds", [
            'json' => $data->toArray(),
            'headers' => $this->requestHeaders(),
        ]);

        return $this->json($response);
    }

    public function tokenizeCard(array $cardData): array
    {
        $client = $this->httpFactory->makeWithToken();

        $response = $client->post('vault/tokenize', [
            'json' => $cardData,
            'headers' => $this->requestHeaders(),
        ]);

        return $this->json($response);
    }

    public function verifyWebhook(array $headers, string $payload): bool
    {
        $signatureHeader = Arr::get($headers, 'x-fnb-signature');
        $timestampHeader = Arr::get($headers, 'x-fnb-timestamp');

        $signature = is_array($signatureHeader) ? $signatureHeader[0] ?? null : $signatureHeader;
        $timestamp = is_array($timestampHeader) ? $timestampHeader[0] ?? null : $timestampHeader;

        if (!$signature || !$timestamp) {
            return false;
        }

        if ($this->verificationStrategy === 'strict') {
            $requestTime = Carbon::createFromTimestamp((int) $timestamp);

            if (now()->diffInSeconds($requestTime) > 300) {
                Log::warning('Stale FNB webhook rejected', [
                    'timestamp' => $timestamp,
                ]);

                return false;
            }
        }

        $computed = hash_hmac('sha256', $timestamp.'.'.$payload, $this->webhookSecret);

        $valid = hash_equals($signature, $computed);

        if (!$valid && $this->verificationStrategy === 'strict') {
            Log::warning('Invalid FNB webhook signature', [
                'signature' => $signature,
                'computed' => $computed,
            ]);
        }

        return $valid;
    }

    private function requestHeaders(): array
    {
        return [
            'Idempotency-Key' => Str::uuid()->toString(),
        ];
    }

    private function json(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }
}

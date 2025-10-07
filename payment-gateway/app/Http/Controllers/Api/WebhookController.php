<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Services\Fnb\FnbClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class WebhookController extends Controller
{
    public function __construct(private readonly FnbClient $client)
    {
    }

    public function __invoke(Request $request, Merchant $merchant): JsonResponse
    {
        if (!$this->client->verifyWebhook($request->headers->all(), $request->getContent())) {
            throw new AccessDeniedHttpException('Invalid signature');
        }

        $payload = $request->json()->all();

        $transaction = Transaction::firstOrCreate(
            ['fnb_payment_id' => $payload['data']['id'] ?? null],
            [
                'id' => (string) Str::uuid(),
                'merchant_id' => $merchant->id,
                'merchant_reference' => $payload['data']['merchant_reference'] ?? null,
                'amount' => $payload['data']['amount'] ?? 0,
                'currency' => $payload['data']['currency'] ?? 'ZAR',
            ]
        );

        $transaction->update([
            'status' => $payload['data']['status'] ?? $transaction->status,
            'captured_at' => $payload['data']['captured_at'] ?? $transaction->captured_at,
            'refunded_at' => $payload['data']['refunded_at'] ?? $transaction->refunded_at,
            'raw_response' => $payload,
        ]);

        return response()->json(['received' => true]);
    }
}

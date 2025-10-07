<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Services\Fnb\Dto\PaymentRequestData;
use App\Services\Fnb\FnbClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private readonly FnbClient $client)
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware(['permission:payments.create'])->only('store');
    }

    public function store(PaymentRequest $request, Merchant $merchant): JsonResponse
    {
        $payload = PaymentRequestData::fromArray($request->validated());

        $response = DB::transaction(function () use ($payload, $merchant) {
            $fnbResponse = $this->client->createPayment($payload);

            $transaction = Transaction::create([
                'id' => (string) Str::uuid(),
                'merchant_id' => $merchant->id,
                'fnb_payment_id' => $fnbResponse['id'] ?? null,
                'merchant_reference' => $payload->merchantReference,
                'amount' => $payload->amount,
                'currency' => $payload->currency,
                'status' => $fnbResponse['status'] ?? 'pending',
                'metadata' => $payload->metadata,
                'raw_response' => $fnbResponse,
            ]);

            return [
                'transaction' => $transaction,
                'fnb' => $fnbResponse,
            ];
        });

        return response()->json([
            'transaction' => $response['transaction'],
            'fnb' => $response['fnb'],
        ], 201);
    }
}

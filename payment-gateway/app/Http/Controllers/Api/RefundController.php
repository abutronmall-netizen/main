<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RefundRequest;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Services\Fnb\Dto\RefundRequestData;
use App\Services\Fnb\FnbClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RefundController extends Controller
{
    public function __construct(private readonly FnbClient $client)
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware(['permission:payments.refund']);
    }

    public function store(RefundRequest $request, Merchant $merchant, string $transactionId): JsonResponse
    {
        $transaction = Transaction::where('merchant_id', $merchant->id)
            ->where('id', $transactionId)
            ->first();

        if (!$transaction) {
            throw new NotFoundHttpException('Transaction not found');
        }

        $payload = RefundRequestData::fromArray(array_merge($request->validated(), [
            'payment_id' => $transaction->fnb_payment_id,
        ]));

        $response = DB::transaction(function () use ($transaction, $payload) {
            $fnbResponse = $this->client->refundPayment($payload);

            $transaction->update([
                'status' => $fnbResponse['status'] ?? 'refunded',
                'refunded_at' => now(),
                'raw_response' => array_merge((array) $transaction->raw_response, [
                    'refund' => $fnbResponse,
                ]),
            ]);

            return $fnbResponse;
        });

        return response()->json([
            'transaction' => $transaction->fresh(),
            'fnb' => $response,
        ]);
    }
}

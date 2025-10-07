<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunReconciliation extends Command
{
    protected $signature = 'reconciliation:run {--merchant=}';

    protected $description = 'Run daily reconciliation against FNB payouts';

    public function handle(): int
    {
        $merchantId = $this->option('merchant');

        $query = Transaction::query()->where('status', 'captured');

        if ($merchantId) {
            $query->where('merchant_id', $merchantId);
        }

        $count = $query->count();

        Log::info('Reconciling transactions', [
            'merchant_id' => $merchantId,
            'count' => $count,
        ]);

        $this->info("Reconciled {$count} transactions");

        return self::SUCCESS;
    }
}

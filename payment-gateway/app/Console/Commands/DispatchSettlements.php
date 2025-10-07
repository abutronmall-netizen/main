<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DispatchSettlements extends Command
{
    protected $signature = 'settlements:dispatch';

    protected $description = 'Generate settlement files for merchants';

    public function handle(): int
    {
        $merchants = Merchant::where('status', 'active')->get();

        foreach ($merchants as $merchant) {
            Log::info('Dispatching settlement', ['merchant_id' => $merchant->id]);
            // TODO: implement settlement file generation.
        }

        $this->info('Settlement dispatch complete.');

        return self::SUCCESS;
    }
}

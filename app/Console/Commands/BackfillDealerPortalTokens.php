<?php

namespace App\Console\Commands;

use App\Models\Dealer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillDealerPortalTokens extends Command
{
    protected $signature = 'dealers:backfill-portal-tokens';

    protected $description = 'Backfill portal_token for dealers missing one';

    public function handle(): int
    {
        DB::transaction(function () {
            Dealer::whereNull('portal_token')->orWhere('portal_token', '')
                ->chunkById(200, function ($chunk) {
                    foreach ($chunk as $d) {
                        $d->portal_token = hash_hmac('sha256', $d->code, config('app.key'));
                        $d->save();
                    }
                });
        });

        $this->info('Backfill complete.');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDealersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kycn:clear-dealers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all dealers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        DB::table('dealers')->delete();

        try {
            DB::table('sqlite_sequence')->where('name', 'dealers')->delete();
        } catch (\Throwable $e) {
            //
        }

        $this->info('All dealers cleared successfully.');

        return self::SUCCESS;
    }
}

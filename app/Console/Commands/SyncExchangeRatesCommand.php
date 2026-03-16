<?php

namespace App\Console\Commands;

use App\Services\ExchangeRates\ExchangeRateSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncExchangeRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commerce:sync-exchange-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store the latest exchange rates required for checkout';

    /**
     * Execute the console command.
     */
    public function handle(ExchangeRateSyncService $exchangeRateSyncService): int
    {
        try {
            $rate = $exchangeRateSyncService->syncPayPalRate();
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Stored %s -> %s rate %s from %s.',
            $rate->from_currency,
            $rate->to_currency,
            $rate->rate,
            $rate->source,
        ));

        return self::SUCCESS;
    }
}

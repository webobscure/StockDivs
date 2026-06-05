<?php

namespace App\Console\Commands;

use App\Models\PortfolioTransaction;
use App\Models\Watchlist;
use App\Services\DividendService;
use Illuminate\Console\Command;

class UpdateDividends extends Command
{
    protected $signature = 'stocks:update-dividends {--ticker=*}';

    protected $description = 'Refresh dividend calendars for tracked tickers.';

    public function handle(DividendService $dividends): int
    {
        $tickers = collect($this->option('ticker'))
            ->merge(PortfolioTransaction::query()->distinct()->pluck('ticker'))
            ->merge(Watchlist::query()->distinct()->pluck('ticker'))
            ->filter()
            ->map(fn (string $ticker) => strtoupper($ticker))
            ->unique()
            ->values();

        $tickers->each(fn (string $ticker) => $dividends->refreshTicker($ticker, true));
        $this->info("Updated dividends for {$tickers->count()} ticker(s).");

        return self::SUCCESS;
    }
}

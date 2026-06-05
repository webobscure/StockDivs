<?php

namespace App\Console\Commands;

use App\Models\PortfolioTransaction;
use App\Models\Watchlist;
use App\Services\StockQuoteService;
use Illuminate\Console\Command;

class UpdateStockQuotes extends Command
{
    protected $signature = 'stocks:update-quotes {--ticker=*}';

    protected $description = 'Refresh cached stock quotes for portfolio and watchlist tickers.';

    public function handle(StockQuoteService $quotes): int
    {
        $tickers = collect($this->option('ticker'))
            ->merge(PortfolioTransaction::query()->distinct()->pluck('ticker'))
            ->merge(Watchlist::query()->distinct()->pluck('ticker'))
            ->filter()
            ->map(fn (string $ticker) => strtoupper($ticker))
            ->unique()
            ->values();

        $tickers->each(fn (string $ticker) => $quotes->getQuote($ticker, true));
        $this->info("Updated {$tickers->count()} quote(s).");

        return self::SUCCESS;
    }
}

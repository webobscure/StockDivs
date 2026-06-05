<?php

namespace App\Services;

use App\Models\Dividend;
use App\Services\MarketData\MarketDataProviderInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class DividendService
{
    public function __construct(private readonly MarketDataProviderInterface $provider) {}

    public function refreshTicker(string $ticker, bool $force = false): Collection
    {
        $ticker = strtoupper($ticker);
        $cacheKey = "dividends.{$this->provider->name()}.{$ticker}";

        $items = $force || ! Cache::has($cacheKey)
            ? $this->provider->dividends($ticker)
            : Cache::get($cacheKey);

        Cache::put($cacheKey, $items, now()->addDay());

        foreach ($items as $item) {
            $providerName = $item['provider'] ?? $this->provider->name();

            Dividend::updateOrCreate(
                [
                    'ticker' => $ticker,
                    'ex_dividend_date' => $item['ex_dividend_date'] ?? null,
                    'payment_date' => $item['payment_date'] ?? null,
                    'provider' => $providerName,
                ],
                [...$item, 'provider' => $providerName],
            );
        }

        return Dividend::query()
            ->where('ticker', $ticker)
            ->orderBy('payment_date')
            ->get();
    }

    public function upcoming(int $limit = 20): Collection
    {
        return Dividend::query()
            ->whereDate('payment_date', '>=', now()->toDateString())
            ->orderBy('payment_date')
            ->limit($limit)
            ->get();
    }
}

<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockQuote;
use App\Services\MarketData\MarketDataProviderInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

class StockQuoteService
{
    public function __construct(private readonly MarketDataProviderInterface $provider) {}

    /** @return array<string, mixed> */
    public function getQuote(string $ticker, bool $force = false): array
    {
        $ticker = strtoupper($ticker);
        $cacheKey = "quotes.{$this->provider->name()}.{$ticker}";

        if (! $force && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $quote = $this->provider->quote($ticker);
        $providerName = $quote['provider'] ?? $this->provider->name();

        Stock::updateOrCreate(
            ['ticker' => $ticker],
            [
                'company_name' => $quote['company_name'] ?? $ticker,
                'exchange' => $quote['exchange'] ?? null,
                'country' => $quote['country'] ?? null,
                'currency' => $quote['currency'] ?? 'USD',
                'description' => $quote['description'] ?? null,
                'provider' => $providerName,
            ],
        );

        StockQuote::updateOrCreate(
            ['ticker' => $ticker, 'provider' => $providerName],
            [
                'price' => $quote['price'],
                'currency' => $quote['currency'] ?? 'USD',
                'change' => $quote['change'] ?? 0,
                'change_percent' => $quote['change_percent'] ?? 0,
                'market_time' => isset($quote['market_time']) ? CarbonImmutable::parse($quote['market_time']) : now(),
            ],
        );

        Cache::put($cacheKey, $quote, now()->addMinutes((int) config('services.market_data.quote_ttl', 10)));

        return $quote;
    }
}

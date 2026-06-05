<?php

namespace App\Services\MarketData;

use App\Models\Stock;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CompositeMarketDataProvider implements MarketDataProviderInterface
{
    public function __construct(
        private readonly MoexMarketDataProvider $moex,
        private readonly FinnhubMarketDataProvider $finnhub,
        private readonly MockMarketDataProvider $mock,
    ) {}

    public function search(string $query): array
    {
        return collect([$this->moex, $this->finnhub, $this->mock])
            ->flatMap(fn (MarketDataProviderInterface $provider) => $this->safe(fn () => $provider->search($query), $provider, []))
            ->unique('ticker')
            ->values()
            ->all();
    }

    public function quote(string $ticker): array
    {
        foreach ($this->quoteProviders($ticker) as $provider) {
            $quote = $this->safe(fn () => $provider->quote($ticker), $provider);

            if ($quote !== null) {
                return $quote;
            }
        }

        throw new MarketDataProviderException("Quote not found for {$ticker}.");
    }

    public function dividends(string $ticker): array
    {
        foreach ($this->quoteProviders($ticker) as $provider) {
            $items = $this->safe(fn () => $provider->dividends($ticker), $provider);

            if (! empty($items)) {
                return $items;
            }
        }

        return [];
    }

    public function exchangeRates(string $baseCurrency): array
    {
        return $this->mock->exchangeRates($baseCurrency);
    }

    public function name(): string
    {
        return 'composite';
    }

    /** @return array<int, MarketDataProviderInterface> */
    private function quoteProviders(string $ticker): array
    {
        if ($this->isMoexTicker($ticker)) {
            return [$this->moex, $this->mock, $this->finnhub];
        }

        return [$this->finnhub, $this->mock, $this->moex];
    }

    private function isMoexTicker(string $ticker): bool
    {
        $ticker = Str::upper($ticker);
        $stock = Stock::query()->where('ticker', $ticker)->first();

        return $stock?->exchange === 'MOEX'
            || $stock?->currency === 'RUB'
            || in_array($ticker, ['SBER', 'GAZP', 'LKOH', 'YDEX'], true);
    }

    private function safe(callable $callback, MarketDataProviderInterface $provider, mixed $fallback = null): mixed
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            Log::warning('Market data provider failed.', [
                'provider' => $provider->name(),
                'message' => $exception->getMessage(),
            ]);

            return $fallback;
        }
    }
}

<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Services\MarketData\MarketDataProviderInterface;
use Illuminate\Support\Facades\Cache;

class ExchangeRateService
{
    public function __construct(private readonly MarketDataProviderInterface $provider) {}

    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return round($amount, 2);
        }

        $rates = $this->rates($fromCurrency);
        $rate = $rates[$toCurrency] ?? 1.0;

        return round($amount * $rate, 2);
    }

    /** @return array<string, float> */
    public function rates(string $baseCurrency, bool $force = false): array
    {
        $baseCurrency = strtoupper($baseCurrency);
        $cacheKey = "exchange_rates.{$this->provider->name()}.{$baseCurrency}";

        if (! $force && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $rates = $this->provider->exchangeRates($baseCurrency);

        foreach ($rates as $quoteCurrency => $rate) {
            ExchangeRate::updateOrCreate(
                [
                    'base_currency' => $baseCurrency,
                    'quote_currency' => strtoupper($quoteCurrency),
                    'provider' => $this->provider->name(),
                ],
                ['rate' => $rate],
            );
        }

        Cache::put($cacheKey, $rates, now()->addDay());

        return $rates;
    }
}

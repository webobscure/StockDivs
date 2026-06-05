<?php

namespace App\Services\MarketData;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FinnhubMarketDataProvider implements MarketDataProviderInterface
{
    public function search(string $query): array
    {
        $payload = $this->request('/search', ['q' => $query]);

        return collect($payload['result'] ?? [])
            ->filter(fn (array $item) => ! empty($item['symbol']))
            ->take(20)
            ->map(fn (array $item) => [
                'ticker' => Str::upper($item['symbol']),
                'company_name' => $item['description'] ?: $item['symbol'],
                'exchange' => null,
                'country' => null,
                'currency' => 'USD',
                'description' => $item['description'] ?? null,
                'provider' => $this->name(),
            ])
            ->values()
            ->all();
    }

    public function quote(string $ticker): array
    {
        $ticker = Str::upper($ticker);
        $quote = $this->request('/quote', ['symbol' => $ticker]);

        if ((float) ($quote['c'] ?? 0) <= 0) {
            throw new MarketDataProviderException("Finnhub quote not found for {$ticker}.");
        }

        $profile = $this->profile($ticker);
        $current = (float) $quote['c'];
        $previousClose = (float) ($quote['pc'] ?? 0);
        $change = $previousClose > 0 ? $current - $previousClose : (float) ($quote['d'] ?? 0);

        return [
            'ticker' => $ticker,
            'company_name' => $profile['name'] ?? $ticker,
            'exchange' => $profile['exchange'] ?? null,
            'country' => $profile['country'] ?? null,
            'currency' => $profile['currency'] ?? 'USD',
            'description' => $profile['finnhubIndustry'] ?? null,
            'price' => $current,
            'change' => round($change, 6),
            'change_percent' => (float) ($quote['dp'] ?? 0),
            'market_time' => ! empty($quote['t']) ? CarbonImmutable::createFromTimestamp((int) $quote['t'])->toISOString() : now()->toISOString(),
            'provider' => $this->name(),
        ];
    }

    public function dividends(string $ticker): array
    {
        $ticker = Str::upper($ticker);
        $payload = $this->request('/stock/dividend', [
            'symbol' => $ticker,
            'from' => now()->subYears(2)->toDateString(),
            'to' => now()->addYear()->toDateString(),
        ]);

        return collect($payload)
            ->map(fn (array $item) => [
                'ticker' => $ticker,
                'amount' => (float) ($item['amount'] ?? 0),
                'currency' => $item['currency'] ?? 'USD',
                'ex_dividend_date' => $item['date'] ?? null,
                'record_date' => $item['recordDate'] ?? null,
                'payment_date' => $item['payDate'] ?? $item['date'] ?? null,
                'declaration_date' => $item['declarationDate'] ?? null,
                'dividend_yield' => null,
                'frequency' => null,
                'provider' => $this->name(),
            ])
            ->filter(fn (array $item) => $item['amount'] > 0 && $item['payment_date'])
            ->values()
            ->all();
    }

    public function exchangeRates(string $baseCurrency): array
    {
        return [];
    }

    public function name(): string
    {
        return 'finnhub';
    }

    private function profile(string $ticker): array
    {
        return $this->request('/stock/profile2', ['symbol' => $ticker]);
    }

    private function request(string $path, array $query): array
    {
        $token = config('services.market_data.finnhub_key');

        if (! $token) {
            throw new MarketDataProviderException('Finnhub API key is not configured.');
        }

        return Http::timeout(10)
            ->retry(2, 200)
            ->get(rtrim((string) config('services.market_data.finnhub_url'), '/').$path, [
                ...$query,
                'token' => $token,
            ])
            ->throw()
            ->json();
    }
}

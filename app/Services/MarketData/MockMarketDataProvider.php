<?php

namespace App\Services\MarketData;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class MockMarketDataProvider implements MarketDataProviderInterface
{
    /** @var array<string, array<string, mixed>> */
    private array $stocks = [
        'AAPL' => ['ticker' => 'AAPL', 'company_name' => 'Apple Inc.', 'exchange' => 'NASDAQ', 'country' => 'US', 'currency' => 'USD', 'description' => 'Consumer technology, services, and devices.'],
        'MSFT' => ['ticker' => 'MSFT', 'company_name' => 'Microsoft Corporation', 'exchange' => 'NASDAQ', 'country' => 'US', 'currency' => 'USD', 'description' => 'Cloud, software, gaming, and enterprise platforms.'],
        'NVDA' => ['ticker' => 'NVDA', 'company_name' => 'NVIDIA Corporation', 'exchange' => 'NASDAQ', 'country' => 'US', 'currency' => 'USD', 'description' => 'Accelerated computing and AI chips.'],
        'TSLA' => ['ticker' => 'TSLA', 'company_name' => 'Tesla, Inc.', 'exchange' => 'NASDAQ', 'country' => 'US', 'currency' => 'USD', 'description' => 'Electric vehicles, energy storage, and software.'],
        'VUSA' => ['ticker' => 'VUSA', 'company_name' => 'Vanguard S&P 500 UCITS ETF', 'exchange' => 'LSE', 'country' => 'GB', 'currency' => 'GBP', 'description' => 'S&P 500 exchange-traded fund.'],
        'SBER' => ['ticker' => 'SBER', 'company_name' => 'Sberbank', 'exchange' => 'MOEX', 'country' => 'RU', 'currency' => 'RUB', 'description' => 'Russian financial services, banking, and digital ecosystem.'],
        'GAZP' => ['ticker' => 'GAZP', 'company_name' => 'Gazprom', 'exchange' => 'MOEX', 'country' => 'RU', 'currency' => 'RUB', 'description' => 'Russian natural gas producer and infrastructure operator.'],
        'LKOH' => ['ticker' => 'LKOH', 'company_name' => 'Lukoil', 'exchange' => 'MOEX', 'country' => 'RU', 'currency' => 'RUB', 'description' => 'Russian integrated oil and gas company.'],
        'YDEX' => ['ticker' => 'YDEX', 'company_name' => 'Yandex', 'exchange' => 'MOEX', 'country' => 'RU', 'currency' => 'RUB', 'description' => 'Search, advertising, mobility, cloud, and consumer internet services.'],
    ];

    /** @var array<string, array{price: float, change: float, change_percent: float}> */
    private array $quotes = [
        'AAPL' => ['price' => 213.92, 'change' => 2.48, 'change_percent' => 1.17],
        'MSFT' => ['price' => 506.74, 'change' => -1.95, 'change_percent' => -0.38],
        'NVDA' => ['price' => 178.81, 'change' => 4.16, 'change_percent' => 2.38],
        'TSLA' => ['price' => 342.56, 'change' => 8.42, 'change_percent' => 2.52],
        'VUSA' => ['price' => 92.18, 'change' => 0.31, 'change_percent' => 0.34],
        'SBER' => ['price' => 318.42, 'change' => 3.12, 'change_percent' => 0.99],
        'GAZP' => ['price' => 132.18, 'change' => -1.04, 'change_percent' => -0.78],
        'LKOH' => ['price' => 6824.50, 'change' => 64.00, 'change_percent' => 0.95],
        'YDEX' => ['price' => 4382.20, 'change' => 41.80, 'change_percent' => 0.96],
    ];

    /** @var array<string, array<int, string>> */
    private array $aliases = [
        'SBER' => ['SBERBANK', 'SBER', 'СБЕР', 'СБЕРБАНК'],
        'GAZP' => ['GAZPROM', 'GAZP', 'ГАЗПРОМ'],
        'LKOH' => ['LUKOIL', 'LUKOIL', 'ЛУКОЙЛ'],
        'YDEX' => ['YANDEX', 'YDEX', 'ЯНДЕКС'],
    ];

    public function search(string $query): array
    {
        $needle = Str::upper($query);

        return array_values(array_filter($this->stocks, function (array $stock) use ($needle): bool {
            $aliases = $this->aliases[$stock['ticker']] ?? [];

            return str_contains($stock['ticker'], $needle)
                || str_contains(Str::upper($stock['company_name']), $needle)
                || collect($aliases)->contains(fn (string $alias) => str_contains($alias, $needle));
        }));
    }

    public function quote(string $ticker): array
    {
        $ticker = strtoupper($ticker);
        $stock = $this->stocks[$ticker] ?? [
            'ticker' => $ticker,
            'company_name' => $ticker,
            'exchange' => null,
            'country' => null,
            'currency' => 'USD',
            'description' => null,
        ];

        $quote = $this->quotes[$ticker] ?? [
            'price' => 50 + (crc32($ticker) % 20000) / 100,
            'change' => ((crc32($ticker.'change') % 800) - 400) / 100,
            'change_percent' => ((crc32($ticker.'percent') % 600) - 300) / 100,
        ];

        return array_merge($stock, $quote, [
            'market_time' => CarbonImmutable::now()->toISOString(),
            'provider' => $this->name(),
        ]);
    }

    public function dividends(string $ticker): array
    {
        $ticker = strtoupper($ticker);
        $currency = $this->stocks[$ticker]['currency'] ?? 'USD';
        $amount = match ($ticker) {
            'MSFT' => 0.83,
            'NVDA' => 0.01,
            'VUSA' => 0.32,
            'SBER' => 34.84,
            'GAZP' => 0.00,
            'LKOH' => 498.00,
            'YDEX' => 0.00,
            'TSLA' => 0.00,
            default => 0.26,
        };

        if ($amount <= 0) {
            return [];
        }

        $now = CarbonImmutable::now();

        return collect(range(0, 3))->map(function (int $quarter) use ($ticker, $currency, $amount, $now): array {
            $exDate = $now->addMonths($quarter * 3 + 1)->startOfMonth()->addDays(10);

            return [
                'ticker' => $ticker,
                'amount' => $amount,
                'currency' => $currency,
                'ex_dividend_date' => $exDate->toDateString(),
                'record_date' => $exDate->addDays(1)->toDateString(),
                'payment_date' => $exDate->addWeeks(4)->toDateString(),
                'declaration_date' => $exDate->subWeeks(3)->toDateString(),
                'dividend_yield' => match ($ticker) {
                    'MSFT' => 0.66,
                    'VUSA' => 1.18,
                    'SBER' => 10.94,
                    'LKOH' => 7.30,
                    default => 0.49,
                },
                'frequency' => 'quarterly',
                'provider' => $this->name(),
            ];
        })->all();
    }

    public function exchangeRates(string $baseCurrency): array
    {
        $baseCurrency = strtoupper($baseCurrency);
        $usd = ['USD' => 1.0, 'EUR' => 0.92, 'GBP' => 0.78, 'RUB' => 88.0];

        if (! isset($usd[$baseCurrency])) {
            return $usd;
        }

        $baseInUsd = $usd[$baseCurrency];

        return collect($usd)->map(fn (float $rate) => round($rate / $baseInUsd, 8))->all();
    }

    public function name(): string
    {
        return 'mock';
    }
}

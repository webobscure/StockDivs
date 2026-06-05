<?php

namespace App\Services;

use App\Models\Stock;
use App\Services\MarketData\MarketDataProviderInterface;

class StockSearchService
{
    public function __construct(private readonly MarketDataProviderInterface $provider) {}

    /** @return array<int, array<string, mixed>> */
    public function search(string $query): array
    {
        $results = $this->provider->search($query);

        foreach ($results as $stock) {
            Stock::updateOrCreate(
                ['ticker' => strtoupper($stock['ticker'])],
                [
                    'company_name' => $stock['company_name'],
                    'exchange' => $stock['exchange'] ?? null,
                    'country' => $stock['country'] ?? null,
                    'currency' => $stock['currency'] ?? 'USD',
                    'description' => $stock['description'] ?? null,
                    'provider' => $this->provider->name(),
                ],
            );
        }

        return $results;
    }
}

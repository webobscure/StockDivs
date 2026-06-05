<?php

namespace App\Services\MarketData;

interface MarketDataProviderInterface
{
    /** @return array<int, array<string, mixed>> */
    public function search(string $query): array;

    /** @return array<string, mixed> */
    public function quote(string $ticker): array;

    /** @return array<int, array<string, mixed>> */
    public function dividends(string $ticker): array;

    /** @return array<string, float> */
    public function exchangeRates(string $baseCurrency): array;

    public function name(): string;
}

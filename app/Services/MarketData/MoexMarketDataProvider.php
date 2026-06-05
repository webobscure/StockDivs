<?php

namespace App\Services\MarketData;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MoexMarketDataProvider implements MarketDataProviderInterface
{
    public function search(string $query): array
    {
        $payload = Http::timeout(10)
            ->retry(2, 200)
            ->get($this->url('/securities.json'), [
                'q' => $query,
                'iss.meta' => 'off',
                'iss.only' => 'securities',
                'securities.columns' => 'secid,shortname,name,emitent_title,primary_boardid,group,type,is_traded',
            ])
            ->throw()
            ->json();

        return collect($this->rows($payload['securities'] ?? []))
            ->filter(fn (array $item) => (int) ($item['is_traded'] ?? 0) === 1)
            ->filter(fn (array $item) => in_array($item['primary_boardid'] ?? null, ['TQBR', 'TQTF', 'TQTD'], true))
            ->map(fn (array $item) => [
                'ticker' => Str::upper($item['secid']),
                'company_name' => $item['emitent_title'] ?: ($item['name'] ?: ($item['shortname'] ?: $item['secid'])),
                'exchange' => 'MOEX',
                'country' => 'RU',
                'currency' => $this->currencyForBoard($item['primary_boardid'] ?? null),
                'description' => $item['name'] ?: $item['shortname'],
                'provider' => $this->name(),
            ])
            ->unique('ticker')
            ->values()
            ->all();
    }

    public function quote(string $ticker): array
    {
        $ticker = Str::upper($ticker);
        $payload = Http::timeout(10)
            ->retry(2, 200)
            ->get($this->url("/engines/stock/markets/shares/securities/{$ticker}.json"), [
                'iss.meta' => 'off',
                'iss.only' => 'securities,marketdata',
                'securities.columns' => 'SECID,SHORTNAME,SECNAME,BOARDID,FACEUNIT',
                'marketdata.columns' => 'SECID,LAST,MARKETPRICE,LCURRENTPRICE,CHANGE,LASTCHANGEPRCNT,TIME,SYSTIME',
            ])
            ->throw()
            ->json();

        $security = collect($this->rows($payload['securities'] ?? []))->first();
        $marketData = collect($this->rows($payload['marketdata'] ?? []))
            ->first(fn (array $row) => $this->price($row) > 0);

        if (! $security || ! $marketData) {
            throw new MarketDataProviderException("MOEX quote not found for {$ticker}.");
        }

        $price = $this->price($marketData);

        return [
            'ticker' => $ticker,
            'company_name' => $security['SECNAME'] ?? $security['SHORTNAME'] ?? $ticker,
            'exchange' => 'MOEX',
            'country' => 'RU',
            'currency' => $this->normalizeCurrency($security['FACEUNIT'] ?? null, $security['BOARDID'] ?? null),
            'description' => $security['SHORTNAME'] ?? null,
            'price' => $price,
            'change' => (float) ($marketData['CHANGE'] ?? 0),
            'change_percent' => (float) ($marketData['LASTCHANGEPRCNT'] ?? 0),
            'market_time' => $this->marketTime($marketData),
            'provider' => $this->name(),
        ];
    }

    public function dividends(string $ticker): array
    {
        $ticker = Str::upper($ticker);
        $payload = Http::timeout(10)
            ->retry(2, 200)
            ->get($this->url("/securities/{$ticker}/dividends.json"), [
                'iss.meta' => 'off',
            ])
            ->throw()
            ->json();

        return collect($this->rows($payload['dividends'] ?? []))
            ->map(fn (array $item) => [
                'ticker' => $ticker,
                'amount' => (float) ($item['value'] ?? 0),
                'currency' => 'RUB',
                'ex_dividend_date' => $item['registryclosedate'] ?? null,
                'record_date' => $item['registryclosedate'] ?? null,
                'payment_date' => $item['date'] ?? $item['registryclosedate'] ?? null,
                'declaration_date' => null,
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
        return 'moex';
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.market_data.moex_url'), '/').$path;
    }

    /** @return array<int, array<string, mixed>> */
    private function rows(array $table): array
    {
        $columns = $table['columns'] ?? [];

        return collect($table['data'] ?? [])
            ->map(fn (array $row) => array_combine($columns, $row) ?: [])
            ->all();
    }

    private function price(array $row): float
    {
        return (float) ($row['LAST'] ?? $row['MARKETPRICE'] ?? $row['LCURRENTPRICE'] ?? 0);
    }

    private function currencyForBoard(?string $board): string
    {
        return in_array($board, ['TQTD'], true) ? 'USD' : 'RUB';
    }

    private function normalizeCurrency(?string $currency, ?string $board): string
    {
        return match ($currency) {
            'SUR', 'RUR' => 'RUB',
            null, '' => $this->currencyForBoard($board),
            default => $currency,
        };
    }

    private function marketTime(array $row): string
    {
        if (! empty($row['SYSTIME'])) {
            return CarbonImmutable::parse($row['SYSTIME'], 'Europe/Moscow')->toISOString();
        }

        return CarbonImmutable::now('Europe/Moscow')->toISOString();
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockQuoteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'ticker' => $this['ticker'],
            'company_name' => $this['company_name'] ?? $this['ticker'],
            'exchange' => $this['exchange'] ?? null,
            'country' => $this['country'] ?? null,
            'currency' => $this['currency'] ?? 'USD',
            'price' => (float) $this['price'],
            'change' => (float) ($this['change'] ?? 0),
            'change_percent' => (float) ($this['change_percent'] ?? 0),
            'description' => $this['description'] ?? null,
            'market_time' => $this['market_time'] ?? null,
            'provider' => $this['provider'] ?? null,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DividendResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticker' => $this->ticker,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'ex_dividend_date' => $this->ex_dividend_date?->toDateString(),
            'record_date' => $this->record_date?->toDateString(),
            'payment_date' => $this->payment_date?->toDateString(),
            'declaration_date' => $this->declaration_date?->toDateString(),
            'dividend_yield' => $this->dividend_yield !== null ? (float) $this->dividend_yield : null,
            'frequency' => $this->frequency,
            'provider' => $this->provider,
        ];
    }
}

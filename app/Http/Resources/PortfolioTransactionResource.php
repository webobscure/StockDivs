<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioTransactionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticker' => $this->ticker,
            'type' => $this->type,
            'quantity' => (float) $this->quantity,
            'price' => (float) $this->price,
            'currency' => $this->currency,
            'transaction_date' => $this->transaction_date?->toDateString(),
            'commission' => (float) $this->commission,
            'notes' => $this->notes,
        ];
    }
}

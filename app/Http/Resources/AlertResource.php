<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticker' => $this->ticker,
            'type' => $this->type,
            'target_value' => $this->target_value !== null ? (float) $this->target_value : null,
            'is_active' => (bool) $this->is_active,
            'triggered_at' => $this->triggered_at?->toISOString(),
        ];
    }
}

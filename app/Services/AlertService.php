<?php

namespace App\Services;

use App\Models\Alert;

class AlertService
{
    public function __construct(private readonly StockQuoteService $quotes) {}

    public function checkActiveAlerts(): int
    {
        $triggered = 0;

        Alert::query()->where('is_active', true)->chunkById(100, function ($alerts) use (&$triggered): void {
            foreach ($alerts as $alert) {
                $quote = $this->quotes->getQuote($alert->ticker);
                $price = (float) $quote['price'];
                $changePercent = abs((float) ($quote['change_percent'] ?? 0));

                $matched = match ($alert->type) {
                    'price_above' => $price >= (float) $alert->target_value,
                    'price_below' => $price <= (float) $alert->target_value,
                    'percent_change' => $changePercent >= (float) $alert->target_value,
                    'dividend_date' => false,
                    default => false,
                };

                if ($matched) {
                    $alert->update(['is_active' => false, 'triggered_at' => now()]);
                    $triggered++;
                }
            }
        });

        return $triggered;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\UserSetting;
use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command
{
    protected $signature = 'exchange-rates:update {--base=*}';

    protected $description = 'Refresh exchange rates for configured base currencies.';

    public function handle(ExchangeRateService $rates): int
    {
        $bases = collect($this->option('base'))
            ->merge(UserSetting::query()->distinct()->pluck('base_currency'))
            ->push('USD')
            ->filter()
            ->map(fn (string $currency) => strtoupper($currency))
            ->unique()
            ->values();

        $bases->each(fn (string $base) => $rates->rates($base, true));
        $this->info("Updated exchange rates for {$bases->count()} base currency/currencies.");

        return self::SUCCESS;
    }
}

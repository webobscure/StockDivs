<?php

namespace App\Providers;

use App\Services\MarketData\CompositeMarketDataProvider;
use App\Services\MarketData\FinnhubMarketDataProvider;
use App\Services\MarketData\MarketDataProviderInterface;
use App\Services\MarketData\MockMarketDataProvider;
use App\Services\MarketData\MoexMarketDataProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MarketDataProviderInterface::class, function ($app): MarketDataProviderInterface {
            return match (config('services.market_data.provider')) {
                'composite' => $app->make(CompositeMarketDataProvider::class),
                'finnhub' => $app->make(FinnhubMarketDataProvider::class),
                'moex' => $app->make(MoexMarketDataProvider::class),
                default => $app->make(MockMarketDataProvider::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

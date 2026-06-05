<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Dividend;
use App\Models\ExchangeRate;
use App\Models\PortfolioTransaction;
use App\Models\Stock;
use App\Models\StockQuote;
use App\Models\User;
use App\Models\Watchlist;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $provider = 'mock';
        $now = CarbonImmutable::now();

        $user = User::query()->firstOrCreate(
            ['email' => 'demo@stockdivs.test'],
            ['name' => 'Demo Investor', 'password' => 'password123'],
        );

        $user->update(['name' => 'Demo Investor', 'password' => 'password123']);

        $user->setting()->updateOrCreate(['user_id' => $user->id], [
            'base_currency' => 'USD',
            'language' => 'en',
            'theme' => 'light',
            'notification_preferences' => [
                'email' => false,
                'price_alerts' => true,
                'dividend_alerts' => true,
            ],
        ]);

        PortfolioTransaction::query()->where('user_id', $user->id)->delete();
        Watchlist::query()->where('user_id', $user->id)->delete();
        Alert::query()->where('user_id', $user->id)->delete();

        $stocks = [
            ['ticker' => 'AAPL', 'company_name' => 'Apple Inc.', 'exchange' => 'NASDAQ', 'country' => 'US', 'currency' => 'USD', 'description' => 'Consumer technology, services, and devices.'],
            ['ticker' => 'MSFT', 'company_name' => 'Microsoft Corporation', 'exchange' => 'NASDAQ', 'country' => 'US', 'currency' => 'USD', 'description' => 'Cloud, software, gaming, and enterprise platforms.'],
            ['ticker' => 'NVDA', 'company_name' => 'NVIDIA Corporation', 'exchange' => 'NASDAQ', 'country' => 'US', 'currency' => 'USD', 'description' => 'Accelerated computing and AI chips.'],
            ['ticker' => 'TSLA', 'company_name' => 'Tesla, Inc.', 'exchange' => 'NASDAQ', 'country' => 'US', 'currency' => 'USD', 'description' => 'Electric vehicles, energy storage, and software.'],
            ['ticker' => 'VUSA', 'company_name' => 'Vanguard S&P 500 UCITS ETF', 'exchange' => 'LSE', 'country' => 'GB', 'currency' => 'GBP', 'description' => 'S&P 500 exchange-traded fund.'],
            ['ticker' => 'SBER', 'company_name' => 'Sberbank', 'exchange' => 'MOEX', 'country' => 'RU', 'currency' => 'RUB', 'description' => 'Russian financial services, banking, and digital ecosystem.'],
            ['ticker' => 'GAZP', 'company_name' => 'Gazprom', 'exchange' => 'MOEX', 'country' => 'RU', 'currency' => 'RUB', 'description' => 'Russian natural gas producer and infrastructure operator.'],
            ['ticker' => 'LKOH', 'company_name' => 'Lukoil', 'exchange' => 'MOEX', 'country' => 'RU', 'currency' => 'RUB', 'description' => 'Russian integrated oil and gas company.'],
            ['ticker' => 'YDEX', 'company_name' => 'Yandex', 'exchange' => 'MOEX', 'country' => 'RU', 'currency' => 'RUB', 'description' => 'Search, advertising, mobility, cloud, and consumer internet services.'],
        ];

        foreach ($stocks as $stock) {
            Stock::updateOrCreate(['ticker' => $stock['ticker']], [...$stock, 'provider' => $provider]);
        }

        $quotes = [
            'AAPL' => ['price' => 213.92, 'currency' => 'USD', 'change' => 2.48, 'change_percent' => 1.17],
            'MSFT' => ['price' => 506.74, 'currency' => 'USD', 'change' => -1.95, 'change_percent' => -0.38],
            'NVDA' => ['price' => 178.81, 'currency' => 'USD', 'change' => 4.16, 'change_percent' => 2.38],
            'TSLA' => ['price' => 342.56, 'currency' => 'USD', 'change' => 8.42, 'change_percent' => 2.52],
            'VUSA' => ['price' => 92.18, 'currency' => 'GBP', 'change' => 0.31, 'change_percent' => 0.34],
            'SBER' => ['price' => 318.42, 'currency' => 'RUB', 'change' => 3.12, 'change_percent' => 0.99],
            'GAZP' => ['price' => 132.18, 'currency' => 'RUB', 'change' => -1.04, 'change_percent' => -0.78],
            'LKOH' => ['price' => 6824.50, 'currency' => 'RUB', 'change' => 64.00, 'change_percent' => 0.95],
            'YDEX' => ['price' => 4382.20, 'currency' => 'RUB', 'change' => 41.80, 'change_percent' => 0.96],
        ];

        foreach ($quotes as $ticker => $quote) {
            StockQuote::updateOrCreate(
                ['ticker' => $ticker, 'provider' => $provider],
                [...$quote, 'market_time' => $now],
            );
        }

        Dividend::query()
            ->whereIn('ticker', array_keys($quotes))
            ->where('provider', $provider)
            ->delete();

        $transactions = [
            ['ticker' => 'AAPL', 'type' => 'buy', 'quantity' => 12, 'price' => 172.50, 'currency' => 'USD', 'transaction_date' => '2025-08-15', 'commission' => 1.00, 'notes' => 'Initial Apple position'],
            ['ticker' => 'AAPL', 'type' => 'buy', 'quantity' => 8, 'price' => 190.10, 'currency' => 'USD', 'transaction_date' => '2026-02-10', 'commission' => 1.00, 'notes' => 'Added before earnings'],
            ['ticker' => 'MSFT', 'type' => 'buy', 'quantity' => 6, 'price' => 421.25, 'currency' => 'USD', 'transaction_date' => '2025-11-20', 'commission' => 1.50, 'notes' => 'Cloud compounder'],
            ['ticker' => 'NVDA', 'type' => 'buy', 'quantity' => 20, 'price' => 112.40, 'currency' => 'USD', 'transaction_date' => '2025-05-12', 'commission' => 2.00, 'notes' => 'AI infrastructure exposure'],
            ['ticker' => 'NVDA', 'type' => 'sell', 'quantity' => 5, 'price' => 161.30, 'currency' => 'USD', 'transaction_date' => '2026-03-04', 'commission' => 1.00, 'notes' => 'Trimmed position'],
            ['ticker' => 'VUSA', 'type' => 'buy', 'quantity' => 30, 'price' => 80.20, 'currency' => 'GBP', 'transaction_date' => '2025-09-03', 'commission' => 3.00, 'notes' => 'Index allocation'],
            ['ticker' => 'SBER', 'type' => 'buy', 'quantity' => 80, 'price' => 278.40, 'currency' => 'RUB', 'transaction_date' => '2025-10-09', 'commission' => 25.00, 'notes' => 'MOEX banking exposure'],
            ['ticker' => 'LKOH', 'type' => 'buy', 'quantity' => 3, 'price' => 6120.00, 'currency' => 'RUB', 'transaction_date' => '2025-12-18', 'commission' => 20.00, 'notes' => 'Russian oil dividend position'],
        ];

        foreach ($transactions as $transaction) {
            PortfolioTransaction::create([...$transaction, 'user_id' => $user->id]);
        }

        $dividends = [
            ['ticker' => 'AAPL', 'amount' => 0.26, 'currency' => 'USD', 'ex_dividend_date' => $now->addDays(24), 'record_date' => $now->addDays(25), 'payment_date' => $now->addDays(47), 'declaration_date' => $now->subDays(12), 'dividend_yield' => 0.49, 'frequency' => 'quarterly'],
            ['ticker' => 'MSFT', 'amount' => 0.83, 'currency' => 'USD', 'ex_dividend_date' => $now->addDays(18), 'record_date' => $now->addDays(19), 'payment_date' => $now->addDays(42), 'declaration_date' => $now->subDays(16), 'dividend_yield' => 0.66, 'frequency' => 'quarterly'],
            ['ticker' => 'NVDA', 'amount' => 0.01, 'currency' => 'USD', 'ex_dividend_date' => $now->addDays(34), 'record_date' => $now->addDays(35), 'payment_date' => $now->addDays(58), 'declaration_date' => $now->subDays(8), 'dividend_yield' => 0.02, 'frequency' => 'quarterly'],
            ['ticker' => 'VUSA', 'amount' => 0.32, 'currency' => 'GBP', 'ex_dividend_date' => $now->addDays(11), 'record_date' => $now->addDays(12), 'payment_date' => $now->addDays(31), 'declaration_date' => $now->subDays(20), 'dividend_yield' => 1.18, 'frequency' => 'quarterly'],
            ['ticker' => 'SBER', 'amount' => 34.84, 'currency' => 'RUB', 'ex_dividend_date' => $now->addDays(37), 'record_date' => $now->addDays(38), 'payment_date' => $now->addDays(68), 'declaration_date' => $now->subDays(24), 'dividend_yield' => 10.94, 'frequency' => 'annual'],
            ['ticker' => 'LKOH', 'amount' => 498.00, 'currency' => 'RUB', 'ex_dividend_date' => $now->addDays(46), 'record_date' => $now->addDays(47), 'payment_date' => $now->addDays(78), 'declaration_date' => $now->subDays(18), 'dividend_yield' => 7.30, 'frequency' => 'semiannual'],
        ];

        foreach ($dividends as $dividend) {
            Dividend::updateOrCreate(
                [
                    'ticker' => $dividend['ticker'],
                    'ex_dividend_date' => $dividend['ex_dividend_date']->toDateString(),
                    'payment_date' => $dividend['payment_date']->toDateString(),
                    'provider' => $provider,
                ],
                [
                    ...$dividend,
                    'ex_dividend_date' => $dividend['ex_dividend_date']->toDateString(),
                    'record_date' => $dividend['record_date']->toDateString(),
                    'payment_date' => $dividend['payment_date']->toDateString(),
                    'declaration_date' => $dividend['declaration_date']->toDateString(),
                    'provider' => $provider,
                ],
            );
        }

        foreach (['TSLA', 'VUSA', 'GAZP', 'YDEX'] as $ticker) {
            $stock = collect($stocks)->firstWhere('ticker', $ticker);
            Watchlist::create([
                'user_id' => $user->id,
                'ticker' => $ticker,
                'company_name' => $stock['company_name'],
                'exchange' => $stock['exchange'],
                'currency' => $stock['currency'],
            ]);
        }

        Alert::create([
            'user_id' => $user->id,
            'ticker' => 'AAPL',
            'type' => 'price_above',
            'target_value' => 225,
            'is_active' => true,
        ]);

        foreach (['USD' => 1, 'EUR' => 0.92, 'GBP' => 0.78, 'RUB' => 88] as $currency => $rate) {
            ExchangeRate::updateOrCreate(
                ['base_currency' => 'USD', 'quote_currency' => $currency, 'provider' => $provider],
                ['rate' => $rate],
            );
        }
    }
}

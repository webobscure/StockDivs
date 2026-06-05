<?php

namespace App\Services;

use App\Models\Dividend;
use App\Models\PortfolioTransaction;
use App\Models\Stock;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PortfolioCalculationService
{
    public function __construct(
        private readonly StockQuoteService $quotes,
        private readonly ExchangeRateService $exchangeRates,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function positions(User $user): array
    {
        return PortfolioTransaction::query()
            ->where('user_id', $user->id)
            ->orderBy('transaction_date')
            ->get()
            ->groupBy('ticker')
            ->map(fn (Collection $transactions, string $ticker) => $this->calculatePosition($user, $ticker, $transactions))
            ->filter(fn (array $position) => $position['quantity'] > 0)
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    public function summary(User $user): array
    {
        $positions = collect($this->positions($user));
        $baseCurrency = $user->setting?->base_currency ?? 'USD';
        $totalInvested = round($positions->sum('invested_amount_base'), 2);
        $totalCurrentValue = round($positions->sum('current_value_base'), 2);
        $totalProfit = round($totalCurrentValue - $totalInvested, 2);
        $dailyChange = round($positions->sum('daily_change_base'), 2);
        $annualDividends = round($positions->sum('expected_annual_dividends_base'), 2);

        return [
            'base_currency' => $baseCurrency,
            'total_invested' => $totalInvested,
            'total_current_value' => $totalCurrentValue,
            'total_profit' => $totalProfit,
            'total_profit_percent' => $totalInvested > 0 ? round($totalProfit / $totalInvested * 100, 2) : 0,
            'daily_change' => $dailyChange,
            'daily_change_percent' => $totalCurrentValue > 0 ? round($dailyChange / $totalCurrentValue * 100, 2) : 0,
            'asset_count' => $positions->count(),
            'expected_monthly_dividends' => round($annualDividends / 12, 2),
            'expected_annual_dividends' => $annualDividends,
            'positions' => $positions->sortByDesc('current_value_base')->values()->all(),
            'allocation' => $positions->map(fn (array $position) => [
                'ticker' => $position['ticker'],
                'value' => $position['current_value_base'],
                'weight' => $totalCurrentValue > 0 ? round($position['current_value_base'] / $totalCurrentValue * 100, 2) : 0,
            ])->values()->all(),
            'currencies' => $positions->groupBy('currency')->map(fn (Collection $items, string $currency) => [
                'currency' => $currency,
                'value' => round($items->sum('current_value_base'), 2),
            ])->values()->all(),
        ];
    }

    public function quantityOnDate(User $user, string $ticker, mixed $date): float
    {
        $targetDate = CarbonImmutable::parse($date);

        $transactions = PortfolioTransaction::query()
            ->where('user_id', $user->id)
            ->where('ticker', strtoupper($ticker))
            ->whereDate('transaction_date', '<=', $targetDate->toDateString())
            ->get();

        $buyQuantity = (float) $transactions->where('type', 'buy')->sum('quantity');
        $sellQuantity = (float) $transactions->where('type', 'sell')->sum('quantity');

        return round($buyQuantity - $sellQuantity, 6);
    }

    /** @param Collection<int, PortfolioTransaction> $transactions */
    private function calculatePosition(User $user, string $ticker, Collection $transactions): array
    {
        $baseCurrency = $user->setting?->base_currency ?? 'USD';
        $buyQuantity = (float) $transactions->where('type', 'buy')->sum('quantity');
        $sellQuantity = (float) $transactions->where('type', 'sell')->sum('quantity');
        $quantity = round($buyQuantity - $sellQuantity, 6);
        $buyCost = $transactions->where('type', 'buy')->sum(fn (PortfolioTransaction $transaction) => ((float) $transaction->quantity * (float) $transaction->price) + (float) $transaction->commission);
        $averageBuyPrice = $buyQuantity > 0 ? round($buyCost / $buyQuantity, 6) : 0;
        $investedAmount = round($averageBuyPrice * $quantity, 2);
        $quote = $this->quotes->getQuote($ticker);
        $currentPrice = (float) $quote['price'];
        $currency = $quote['currency'] ?? $transactions->first()->currency ?? 'USD';
        $currentValue = round($quantity * $currentPrice, 2);
        $unrealizedProfit = round($currentValue - $investedAmount, 2);
        $dailyChange = round($quantity * (float) ($quote['change'] ?? 0), 2);
        $stock = Stock::query()->where('ticker', $ticker)->first();
        $annualDividendPerShare = (float) Dividend::query()
            ->where('ticker', $ticker)
            ->latest('ex_dividend_date')
            ->limit(4)
            ->get()
            ->sum('amount');

        return [
            'ticker' => $ticker,
            'company_name' => $stock?->company_name ?? $quote['company_name'] ?? $ticker,
            'exchange' => $stock?->exchange ?? $quote['exchange'] ?? null,
            'currency' => $currency,
            'quantity' => $quantity,
            'average_buy_price' => $averageBuyPrice,
            'invested_amount' => $investedAmount,
            'invested_amount_base' => $this->exchangeRates->convert($investedAmount, $currency, $baseCurrency),
            'current_price' => $currentPrice,
            'current_value' => $currentValue,
            'current_value_base' => $this->exchangeRates->convert($currentValue, $currency, $baseCurrency),
            'unrealized_profit' => $unrealizedProfit,
            'unrealized_profit_percent' => $investedAmount > 0 ? round($unrealizedProfit / $investedAmount * 100, 2) : 0,
            'daily_change' => $dailyChange,
            'daily_change_base' => $this->exchangeRates->convert($dailyChange, $currency, $baseCurrency),
            'dividend_yield' => $currentPrice > 0 ? round($annualDividendPerShare / $currentPrice * 100, 2) : 0,
            'expected_annual_dividends' => round($annualDividendPerShare * $quantity, 2),
            'expected_annual_dividends_base' => $this->exchangeRates->convert($annualDividendPerShare * $quantity, $currency, $baseCurrency),
        ];
    }
}

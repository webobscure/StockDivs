<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DividendResource;
use App\Models\Dividend;
use App\Services\DividendService;
use App\Services\PortfolioCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DividendController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DividendResource::collection(Dividend::query()->latest('payment_date')->paginate(30));
    }

    public function calendar(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'ticker' => ['nullable', 'string', 'max:24'],
        ]);

        $query = Dividend::query()->orderBy('payment_date');

        if (! empty($data['month'])) {
            $query->whereYear('payment_date', substr($data['month'], 0, 4))
                ->whereMonth('payment_date', substr($data['month'], 5, 2));
        }

        if (! empty($data['ticker'])) {
            $query->where('ticker', strtoupper($data['ticker']));
        }

        return DividendResource::collection($query->get());
    }

    public function upcoming(Request $request, DividendService $dividends, PortfolioCalculationService $calculator): JsonResponse
    {
        $items = $dividends->upcoming()
            ->map(function (Dividend $dividend) use ($request, $calculator): array {
                $heldQuantity = $calculator->quantityOnDate(
                    $request->user(),
                    $dividend->ticker,
                    $dividend->ex_dividend_date ?? now(),
                );

                return [
                    ...((new DividendResource($dividend))->resolve()),
                    'held_quantity' => $heldQuantity,
                    'expected_amount' => round($heldQuantity * (float) $dividend->amount, 2),
                ];
            });

        return response()->json(['data' => $items]);
    }

    public function history(string $ticker): AnonymousResourceCollection
    {
        return DividendResource::collection(
            Dividend::query()->where('ticker', strtoupper($ticker))->latest('payment_date')->get(),
        );
    }

    public function summary(Request $request, PortfolioCalculationService $calculator): JsonResponse
    {
        $summary = $calculator->summary($request->user());

        return response()->json([
            'data' => [
                'expected_monthly_dividends' => $summary['expected_monthly_dividends'],
                'expected_annual_dividends' => $summary['expected_annual_dividends'],
                'base_currency' => $summary['base_currency'],
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PortfolioTransactionRequest;
use App\Http\Resources\PortfolioTransactionResource;
use App\Models\PortfolioTransaction;
use App\Services\PortfolioCalculationService;
use App\Services\PortfolioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PortfolioController extends Controller
{
    public function index(Request $request, PortfolioCalculationService $calculator): JsonResponse
    {
        return response()->json([
            'base_currency' => $request->user()->setting?->base_currency ?? 'USD',
            'data' => $calculator->positions($request->user()),
        ]);
    }

    public function summary(Request $request, PortfolioCalculationService $calculator): JsonResponse
    {
        return response()->json(['data' => $calculator->summary($request->user())]);
    }

    public function transactions(Request $request): AnonymousResourceCollection
    {
        return PortfolioTransactionResource::collection(
            PortfolioTransaction::query()
                ->where('user_id', $request->user()->id)
                ->latest('transaction_date')
                ->get(),
        );
    }

    public function storeTransaction(PortfolioTransactionRequest $request, PortfolioService $portfolio): PortfolioTransactionResource
    {
        return new PortfolioTransactionResource($portfolio->createTransaction($request->user(), $request->validated()));
    }

    public function updateTransaction(PortfolioTransactionRequest $request, PortfolioTransaction $transaction, PortfolioService $portfolio): PortfolioTransactionResource
    {
        $this->abortIfNotOwner($request, $transaction);

        return new PortfolioTransactionResource($portfolio->updateTransaction($transaction, $request->validated()));
    }

    public function deleteTransaction(Request $request, PortfolioTransaction $transaction): JsonResponse
    {
        $this->abortIfNotOwner($request, $transaction);
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted.']);
    }

    public function showTicker(Request $request, string $ticker, PortfolioCalculationService $calculator): JsonResponse
    {
        $position = collect($calculator->positions($request->user()))
            ->firstWhere('ticker', strtoupper($ticker));

        return response()->json([
            'base_currency' => $request->user()->setting?->base_currency ?? 'USD',
            'data' => $position,
            'transactions' => PortfolioTransactionResource::collection(
                PortfolioTransaction::query()
                    ->where('user_id', $request->user()->id)
                    ->where('ticker', strtoupper($ticker))
                    ->orderByDesc('transaction_date')
                    ->get(),
            ),
        ]);
    }

    private function abortIfNotOwner(Request $request, PortfolioTransaction $transaction): void
    {
        abort_unless($transaction->user_id === $request->user()->id, 403);
    }
}

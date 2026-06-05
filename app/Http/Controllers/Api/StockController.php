<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DividendResource;
use App\Http\Resources\StockQuoteResource;
use App\Services\DividendService;
use App\Services\StockQuoteService;
use App\Services\StockSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockController extends Controller
{
    public function search(Request $request, StockSearchService $search, StockQuoteService $quotes): JsonResponse
    {
        $data = $request->validate(['query' => ['required', 'string', 'min:1']]);

        $results = collect($search->search($data['query']))
            ->map(function (array $stock) use ($quotes): array {
                $quote = $quotes->getQuote($stock['ticker']);

                return array_merge($stock, [
                    'price' => $quote['price'],
                    'change' => $quote['change'] ?? 0,
                    'change_percent' => $quote['change_percent'] ?? 0,
                ]);
            })
            ->values();

        return response()->json(['data' => $results]);
    }

    public function show(string $ticker, StockQuoteService $quotes): StockQuoteResource
    {
        return new StockQuoteResource($quotes->getQuote($ticker));
    }

    public function quote(string $ticker, StockQuoteService $quotes): StockQuoteResource
    {
        return new StockQuoteResource($quotes->getQuote($ticker));
    }

    public function dividends(string $ticker, DividendService $dividends): AnonymousResourceCollection
    {
        return DividendResource::collection($dividends->refreshTicker($ticker));
    }
}

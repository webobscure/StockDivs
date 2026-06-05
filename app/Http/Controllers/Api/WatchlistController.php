<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WatchlistRequest;
use App\Http\Resources\WatchlistResource;
use App\Models\Watchlist;
use App\Services\StockQuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function index(Request $request, StockQuoteService $quotes): JsonResponse
    {
        $items = Watchlist::query()->where('user_id', $request->user()->id)->get()
            ->map(function (Watchlist $item) use ($quotes): array {
                $quote = $quotes->getQuote($item->ticker);

                return [
                    ...((new WatchlistResource($item))->resolve()),
                    'price' => (float) $quote['price'],
                    'change_percent' => (float) ($quote['change_percent'] ?? 0),
                ];
            });

        return response()->json(['data' => $items]);
    }

    public function store(WatchlistRequest $request): WatchlistResource
    {
        $data = $request->validated();
        $item = Watchlist::updateOrCreate(
            ['user_id' => $request->user()->id, 'ticker' => strtoupper($data['ticker'])],
            [
                'company_name' => $data['company_name'] ?? strtoupper($data['ticker']),
                'exchange' => $data['exchange'] ?? null,
                'currency' => strtoupper($data['currency']),
            ],
        );

        return new WatchlistResource($item);
    }

    public function destroy(Request $request, string $ticker): JsonResponse
    {
        Watchlist::query()
            ->where('user_id', $request->user()->id)
            ->where('ticker', strtoupper($ticker))
            ->delete();

        return response()->json(['message' => 'Watchlist item deleted.']);
    }
}

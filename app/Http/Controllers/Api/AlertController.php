<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlertRequest;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return AlertResource::collection(
            Alert::query()->where('user_id', $request->user()->id)->latest()->get(),
        );
    }

    public function store(AlertRequest $request): AlertResource
    {
        $data = $request->validated();

        return new AlertResource(Alert::create([
            ...$data,
            'ticker' => strtoupper($data['ticker']),
            'user_id' => $request->user()->id,
            'is_active' => $data['is_active'] ?? true,
        ]));
    }

    public function update(AlertRequest $request, Alert $alert): AlertResource
    {
        $this->abortIfNotOwner($request, $alert);
        $data = $request->validated();
        $data['ticker'] = strtoupper($data['ticker']);
        $alert->update($data);

        return new AlertResource($alert);
    }

    public function destroy(Request $request, Alert $alert): JsonResponse
    {
        $this->abortIfNotOwner($request, $alert);
        $alert->delete();

        return response()->json(['message' => 'Alert deleted.']);
    }

    private function abortIfNotOwner(Request $request, Alert $alert): void
    {
        abort_unless($alert->user_id === $request->user()->id, 403);
    }
}

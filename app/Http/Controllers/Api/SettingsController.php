<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->setting()->firstOrCreate(
                ['user_id' => $request->user()->id],
                ['base_currency' => 'USD', 'language' => 'en', 'theme' => 'light'],
            ),
        ]);
    }

    public function update(SettingsRequest $request): JsonResponse
    {
        $setting = $request->user()->setting()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated(),
        );

        return response()->json(['data' => $setting]);
    }
}

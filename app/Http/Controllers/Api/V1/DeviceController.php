<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Registers the phone's Firebase Cloud Messaging token so the customer can get
 * push notifications, and removes it again on sign-out.
 */
class DeviceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
        ]);

        // A phone that signs in to another account moves its token to that account.
        DeviceToken::updateOrCreate(
            ['token_hash' => DeviceToken::hashOf($data['token'])],
            [
                'user_id' => $request->user()->getKey(),
                'token' => $data['token'],
                'platform' => $data['platform'] ?? 'android',
            ],
        );

        return response()->json(['message' => 'This device will receive notifications.'], 201);
    }

    public function destroy(Request $request): Response
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:4096']]);

        DeviceToken::query()
            ->where('user_id', $request->user()->getKey())
            ->where('token_hash', DeviceToken::hashOf($data['token']))
            ->delete();

        return response()->noContent();
    }
}

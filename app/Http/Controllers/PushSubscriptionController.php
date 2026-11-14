<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Stores and removes browser push subscriptions for administrators.
 */
class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $data = $request->validate([
            'endpoint' => ['required', 'url:https', 'max:1000'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $data['endpoint'])],
            [
                'user_id' => $request->user()->getKey(),
                'endpoint' => $data['endpoint'],
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            ],
        );

        return response()->json(['message' => 'Push notifications turned on.'], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $data = $request->validate(['endpoint' => ['required', 'string', 'max:1000']]);

        PushSubscription::query()
            ->where('user_id', $request->user()->getKey())
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->delete();

        return response()->json(['message' => 'Push notifications turned off.']);
    }
}

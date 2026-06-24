<?php

namespace App\Actions;

use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorePushSubscriptionAction
{
    public function __invoke(Request $request, Guest $guest): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'content_encoding' => ['nullable', 'string', 'max:50'],
        ]);

        $guest->updatePushSubscription(
            endpoint: $validated['endpoint'],
            key: $validated['keys']['p256dh'],
            token: $validated['keys']['auth'],
            contentEncoding: $validated['content_encoding'] ?? null,
        );

        return response()->json(['success' => true]);
    }
}

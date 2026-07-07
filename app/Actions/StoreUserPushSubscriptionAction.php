<?php

namespace App\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreUserPushSubscriptionAction
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'content_encoding' => ['nullable', 'string', 'max:50'],
            'device_label' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();

        $subscription = $user->updatePushSubscription(
            endpoint: $validated['endpoint'],
            key: $validated['keys']['p256dh'],
            token: $validated['keys']['auth'],
            contentEncoding: $validated['content_encoding'] ?? null,
        );

        if (filled($validated['device_label'] ?? null)) {
            $subscription->device_label = $validated['device_label'];
            $subscription->save();
        }

        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\WeddingEvent;
use Illuminate\Http\JsonResponse;

class InvitationManifestController
{
    public function __invoke(string $slug, string $token): JsonResponse
    {
        $event = WeddingEvent::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $event->guests()
            ->where('token', $token)
            ->firstOrFail();

        return response()->json([
            'name' => $event->couple_names,
            'short_name' => $event->couple_names,
            'start_url' => route('invitation.guest', [$event->slug, $token]),
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#1a1208',
            'theme_color' => '#1a1208',
            'icons' => [
                [
                    'src' => '/icons/nd-logo-transparent.webp',
                    'sizes' => '192x192',
                    'type' => 'image/webp',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/icons/nd-logo.webp',
                    'sizes' => '512x512',
                    'type' => 'image/webp',
                    'purpose' => 'any',
                ],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }
}

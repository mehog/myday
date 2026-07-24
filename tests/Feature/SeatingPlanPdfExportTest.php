<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Tests\TestCase;

class SeatingPlanPdfExportTest extends TestCase
{
    public function test_authenticated_user_can_export_seating_plan_pdf_without_session_image(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'seating_plan' => [
                'tables' => [
                    [
                        'id' => 'table-1',
                        'label' => 'Table 1',
                        'type' => 'round',
                        'chair_count' => 2,
                        'seats' => [null, null],
                    ],
                ],
            ],
        ]);

        Guest::query()->create([
            'wedding_event_id' => $event->id,
            'name' => 'Guest One',
            'token' => 'seating-pdf-export-token-1234567',
            'rsvp_status' => RsvpStatus::Yes,
            'plus_one_allowed' => false,
        ]);

        $response = $this->actingAs($user)
            ->get(route('seating-plan.export-pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent() ?: '');
        $this->assertFalse(session()->has('seating_plan_pdf_image'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\WeddingEvent;
use Illuminate\Http\Response;

class WeddingEventCalendarController extends Controller
{
    public function __invoke(string $slug): Response
    {
        $event = WeddingEvent::query()->where('slug', $slug)->firstOrFail();

        $dtstart = $event->wedding_date->format('Ymd');
        $dtend = $event->wedding_date->copy()->addDay()->format('Ymd');
        $summary = __('invitation.save_the_date').' — '.$event->couple_names;
        $location = trim("{$event->location_name} {$event->location_address}");

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//MyDay//EN',
            'BEGIN:VEVENT',
            "UID:{$event->slug}@myday",
            "DTSTART;VALUE=DATE:{$dtstart}",
            "DTEND;VALUE=DATE:{$dtend}",
            "SUMMARY:{$summary}",
            "LOCATION:{$location}",
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$event->slug}.ics\"",
        ]);
    }
}

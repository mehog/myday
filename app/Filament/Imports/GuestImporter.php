<?php

namespace App\Filament\Imports;

use App\Models\Guest;
use App\Models\WeddingEvent;
use League\Csv\Reader;

class GuestImporter
{
    public static function importFromContents(WeddingEvent $event, string $contents): int
    {
        $reader = Reader::createFromString($contents);
        $reader->setHeaderOffset(0);

        $count = 0;

        foreach ($reader->getRecords() as $record) {
            $name = trim($record['name'] ?? $record['Name'] ?? '');

            if ($name === '') {
                continue;
            }

            Guest::query()->create([
                'wedding_event_id' => $event->id,
                'name' => $name,
                'email' => trim($record['email'] ?? $record['Email'] ?? '') ?: null,
                'phone' => trim($record['phone'] ?? $record['Phone'] ?? '') ?: null,
            ]);

            $count++;
        }

        return $count;
    }
}

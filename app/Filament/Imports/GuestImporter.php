<?php

namespace App\Filament\Imports;

use App\Exceptions\GuestLimitExceededException;
use App\Models\Guest;
use App\Models\WeddingEvent;
use League\Csv\Reader;
use RuntimeException;

class GuestImporter
{
    public static function importFromContents(WeddingEvent $event, string $contents): int
    {
        $reader = Reader::createFromString($contents);
        $reader->setHeaderOffset(0);

        $rows = [];

        foreach ($reader->getRecords() as $record) {
            $name = trim($record['name'] ?? $record['Name'] ?? '');

            if ($name === '') {
                continue;
            }

            $rows[] = [
                'name' => $name,
                'email' => trim($record['email'] ?? $record['Email'] ?? '') ?: null,
                'phone' => trim($record['phone'] ?? $record['Phone'] ?? '') ?: null,
            ];
        }

        $incoming = count($rows);

        if ($incoming === 0) {
            return 0;
        }

        if (! $event->canAddGuests($incoming)) {
            $remaining = $event->remainingGuestSlots();

            throw new RuntimeException(
                __('pricing.guest_limit_import', [
                    'remaining' => $remaining ?? 0,
                ])
            );
        }

        $count = 0;

        foreach ($rows as $row) {
            try {
                Guest::query()->create([
                    'wedding_event_id' => $event->id,
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                ]);
            } catch (GuestLimitExceededException $e) {
                throw new RuntimeException(
                    __('pricing.guest_limit_import', [
                        'remaining' => $event->fresh()?->remainingGuestSlots() ?? 0,
                    ]),
                    previous: $e
                );
            }

            $count++;
        }

        return $count;
    }
}

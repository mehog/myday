<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestChild;
use App\Models\WeddingEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SyncGuestChildren
{
    /**
     * Sync children from guest-submitted names, preserving order-matched IDs.
     *
     * @param  list<string|null>  $names
     */
    public function syncFromNames(Guest $guest, array $names): void
    {
        $normalized = collect($names)
            ->map(fn ($name): string => trim((string) $name))
            ->filter(fn (string $name): bool => $name !== '')
            ->take(GuestChild::MAX_PER_GUEST)
            ->values()
            ->map(fn (string $name, int $index): array => [
                'name' => $name,
                'sort_order' => $index,
            ])
            ->all();

        $this->sync($guest, $normalized, preserveSeatingNames: true);
    }

    /**
     * Sync children from couple-managed rows.
     *
     * @param  list<array{id?: int|string|null, name: string, seating_name?: string|null, sort_order?: int}>  $children
     */
    public function syncFromAdmin(Guest $guest, array $children): void
    {
        $normalized = collect($children)
            ->map(function (array $child, int $index): ?array {
                $name = trim((string) ($child['name'] ?? ''));

                if ($name === '') {
                    return null;
                }

                $seatingName = filled($child['seating_name'] ?? null)
                    ? trim((string) $child['seating_name'])
                    : null;

                return [
                    'id' => filled($child['id'] ?? null) ? (int) $child['id'] : null,
                    'name' => $name,
                    'seating_name' => $seatingName,
                    'sort_order' => $child['sort_order'] ?? $index,
                ];
            })
            ->filter()
            ->take(GuestChild::MAX_PER_GUEST)
            ->values()
            ->all();

        $this->sync($guest, $normalized, preserveSeatingNames: false);
    }

    /**
     * @param  list<array{id?: int|null, name: string, seating_name?: string|null, sort_order?: int}>  $children
     */
    protected function sync(Guest $guest, array $children, bool $preserveSeatingNames): void
    {
        DB::transaction(function () use ($guest, $children, $preserveSeatingNames): void {
            $existing = $guest->children()->orderBy('sort_order')->orderBy('id')->get();
            $keptIds = [];

            foreach ($children as $index => $childData) {
                $sortOrder = (int) ($childData['sort_order'] ?? $index);
                $match = null;

                if (isset($childData['id']) && $childData['id'] !== null) {
                    $match = $existing->firstWhere('id', $childData['id']);
                }

                if ($match === null && $preserveSeatingNames) {
                    $match = $existing->get($index);
                }

                if ($match instanceof GuestChild) {
                    $payload = [
                        'name' => $childData['name'],
                        'sort_order' => $sortOrder,
                    ];

                    if (! $preserveSeatingNames) {
                        $payload['seating_name'] = $childData['seating_name'] ?? null;
                    }

                    $match->update($payload);
                    $keptIds[] = $match->id;

                    continue;
                }

                $created = $guest->children()->create([
                    'name' => $childData['name'],
                    'seating_name' => $preserveSeatingNames ? null : ($childData['seating_name'] ?? null),
                    'sort_order' => $sortOrder,
                ]);

                $keptIds[] = $created->id;
            }

            /** @var Collection<int, GuestChild> $removed */
            $removed = $existing->filter(
                fn (GuestChild $child): bool => ! in_array($child->id, $keptIds, true),
            );
            $removedIds = $removed->pluck('id')->map(fn ($id): int => (int) $id)->values()->all();

            if ($removedIds !== []) {
                $this->pruneSeatingAssignments(
                    WeddingEvent::query()->find($guest->wedding_event_id),
                    $removedIds,
                );
                GuestChild::query()->whereIn('id', $removedIds)->delete();
            }
        });
    }

    /**
     * @param  list<int>  $removedChildIds
     */
    public function pruneSeatingAssignments(?WeddingEvent $wedding, array $removedChildIds): void
    {
        if (! $wedding instanceof WeddingEvent || $removedChildIds === []) {
            return;
        }

        $plan = json_decode(json_encode($wedding->seating_plan), true);

        if (! is_array($plan) || ! isset($plan['tables']) || ! is_array($plan['tables'])) {
            return;
        }

        $keys = array_map(
            fn (int $id): string => 'child:'.$id,
            $removedChildIds,
        );

        $changed = false;

        foreach ($plan['tables'] as $tableIndex => $table) {
            if (! is_array($table) || ! isset($table['seats']) || ! is_array($table['seats'])) {
                continue;
            }

            foreach ($table['seats'] as $seatIndex => $seat) {
                if ($seat === null || $seat === '') {
                    continue;
                }

                if (in_array((string) $seat, $keys, true)) {
                    $plan['tables'][$tableIndex]['seats'][$seatIndex] = null;
                    $changed = true;
                }
            }
        }

        if ($changed) {
            $wedding->forceFill(['seating_plan' => $plan])->save();
        }
    }
}

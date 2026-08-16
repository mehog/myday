<?php

namespace App\Support;

final class OnboardingSteps
{
    /**
     * @return list<array{id: string, counted: bool, optional: bool}>
     */
    public static function all(): array
    {
        return [
            ['id' => 'names', 'counted' => true, 'optional' => false],
            ['id' => 'date', 'counted' => true, 'optional' => false],
            ['id' => 'tip-rsvp', 'counted' => false, 'optional' => false],
            ['id' => 'theme', 'counted' => true, 'optional' => false],
            ['id' => 'template', 'counted' => true, 'optional' => false],
            ['id' => 'reveal', 'counted' => true, 'optional' => true],
            ['id' => 'location', 'counted' => true, 'optional' => true],
            ['id' => 'tip-budget', 'counted' => false, 'optional' => false],
            ['id' => 'motto', 'counted' => true, 'optional' => true],
            ['id' => 'cover', 'counted' => true, 'optional' => true],
            ['id' => 'tip-photos', 'counted' => false, 'optional' => false],
            ['id' => 'song', 'counted' => true, 'optional' => true],
            ['id' => 'schedule', 'counted' => true, 'optional' => true],
            ['id' => 'tip-seating', 'counted' => false, 'optional' => false],
            ['id' => 'guests', 'counted' => true, 'optional' => true],
            ['id' => 'tip-menus', 'counted' => false, 'optional' => false],
            ['id' => 'account', 'counted' => true, 'optional' => false],
            ['id' => 'review', 'counted' => true, 'optional' => false],
        ];
    }

    /**
     * @return list<string>
     */
    public static function ids(): array
    {
        return array_column(self::all(), 'id');
    }

    public static function isValid(string $step): bool
    {
        return in_array($step, self::ids(), true);
    }

    public static function isCounted(string $step): bool
    {
        foreach (self::all() as $definition) {
            if ($definition['id'] === $step) {
                return $definition['counted'];
            }
        }

        return true;
    }

    public static function isOptional(string $step): bool
    {
        foreach (self::all() as $definition) {
            if ($definition['id'] === $step) {
                return $definition['optional'];
            }
        }

        return false;
    }

    public static function isTip(string $step): bool
    {
        return str_starts_with($step, 'tip-');
    }

    public static function indexOf(string $step): int
    {
        $index = array_search($step, self::ids(), true);

        return $index === false ? 0 : $index;
    }

    public static function countedTotal(): int
    {
        return count(array_filter(self::all(), fn (array $step): bool => $step['counted']));
    }

    /**
     * 1-based counted progress position for the given step (tips freeze on the last counted step).
     */
    public static function countedPosition(string $step): int
    {
        $position = 0;

        foreach (self::all() as $definition) {
            if ($definition['counted']) {
                $position++;
            }

            if ($definition['id'] === $step) {
                return max(1, $position);
            }
        }

        return max(1, $position);
    }

    public static function next(string $step): ?string
    {
        $ids = self::ids();
        $index = self::indexOf($step);

        return $ids[$index + 1] ?? null;
    }

    public static function previous(string $step): ?string
    {
        $ids = self::ids();
        $index = self::indexOf($step);

        return $index > 0 ? $ids[$index - 1] : null;
    }

    /**
     * Whether the user may open this step given completed required fields so far.
     *
     * @param  array{groom_name?: string, bride_name?: string, wedding_date?: string, theme?: string, template?: string, your_name?: string, email?: string}  $data
     */
    public static function canAccess(string $step, array $data): bool
    {
        if (! self::isValid($step)) {
            return false;
        }

        $targetIndex = self::indexOf($step);

        foreach (self::all() as $definition) {
            if (self::indexOf($definition['id']) >= $targetIndex) {
                break;
            }

            if ($definition['optional'] || self::isTip($definition['id'])) {
                continue;
            }

            if (! self::hasRequiredDataForStep($definition['id'], $data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array{groom_name?: string, bride_name?: string, wedding_date?: string, theme?: string, template?: string, your_name?: string, email?: string}  $data
     */
    public static function firstIncompleteStep(array $data): string
    {
        foreach (self::all() as $definition) {
            if ($definition['optional'] || self::isTip($definition['id'])) {
                continue;
            }

            if ($definition['id'] === 'review') {
                return 'review';
            }

            if (! self::hasRequiredDataForStep($definition['id'], $data)) {
                return $definition['id'];
            }
        }

        return 'review';
    }

    /**
     * @param  array{groom_name?: string, bride_name?: string, wedding_date?: string, theme?: string, template?: string, your_name?: string, email?: string}  $data
     */
    public static function hasRequiredDataForStep(string $step, array $data): bool
    {
        return match ($step) {
            'names' => filled($data['groom_name'] ?? null) && filled($data['bride_name'] ?? null),
            'date' => filled($data['wedding_date'] ?? null),
            'theme' => filled($data['theme'] ?? null),
            'template' => filled($data['template'] ?? null),
            'account' => filled($data['your_name'] ?? null) && filled($data['email'] ?? null),
            default => true,
        };
    }

    /**
     * Map a validation attribute to the onboarding step that owns it.
     */
    public static function stepForField(string $field): string
    {
        $root = explode('.', $field)[0];

        return match ($root) {
            'groom_name', 'bride_name' => 'names',
            'wedding_date' => 'date',
            'theme' => 'theme',
            'template' => 'template',
            'reveal_animation' => 'reveal',
            'location_name', 'location_address' => 'location',
            'motto' => 'motto',
            'hero_image' => 'cover',
            'music_url' => 'song',
            'scheduleItems' => 'schedule',
            'guests' => 'guests',
            'your_name', 'email', 'password', 'password_confirmation' => 'account',
            default => 'review',
        };
    }
}

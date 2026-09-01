<?php

namespace App\Filament\Resources\WeddingEvents\RelationManagers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait ShowsRelationshipCountBadge
{
    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = static::resolveRelationshipCount($ownerRecord);

        return $count > 0 ? (string) $count : null;
    }

    public static function getBadgeColor(Model $ownerRecord, string $pageClass): ?string
    {
        return static::resolveRelationshipCount($ownerRecord) > 0 ? 'primary' : null;
    }

    protected static function resolveRelationshipCount(Model $ownerRecord): int
    {
        $relationship = static::$relationship;
        $countAttribute = Str::snake($relationship).'_count';

        if (array_key_exists($countAttribute, $ownerRecord->getAttributes())) {
            return (int) $ownerRecord->getAttributes()[$countAttribute];
        }

        return $ownerRecord->{$relationship}()->count();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestChild extends Model
{
    public const MAX_PER_GUEST = 10;

    protected $fillable = [
        'guest_id',
        'name',
        'seating_name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function displayName(): string
    {
        return filled($this->seating_name)
            ? $this->seating_name
            : $this->name;
    }

    public function seatingAssigneeKey(): string
    {
        return 'child:'.$this->id;
    }

    public static function idFromSeatingAssigneeKey(string $key): ?int
    {
        if (! str_starts_with($key, 'child:')) {
            return null;
        }

        $id = substr($key, 6);

        return ctype_digit($id) ? (int) $id : null;
    }
}

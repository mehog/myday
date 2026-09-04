<?php

namespace App\Models;

use App\WeddingMemberRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingMember extends Model
{
    protected $fillable = [
        'wedding_event_id',
        'user_id',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'role' => WeddingMemberRole::class,
        ];
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

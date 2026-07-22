<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DodoWebhookEvent extends Model
{
    protected $fillable = [
        'webhook_id',
        'event_type',
        'status',
        'payload',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function markProcessed(): void
    {
        $this->forceFill([
            'status' => 'processed',
            'processed_at' => now(),
            'error_message' => null,
        ])->save();
    }

    public function markFailed(string $message): void
    {
        $this->forceFill([
            'status' => 'failed',
            'error_message' => $message,
            'processed_at' => now(),
        ])->save();
    }
}

<?php

namespace App\Models;

use App\BudgetCalculationType;
use App\BudgetCategory;
use Database\Factories\WeddingBudgetItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingBudgetItem extends Model
{
    /** @use HasFactory<WeddingBudgetItemFactory> */
    use HasFactory;

    protected $fillable = [
        'wedding_event_id',
        'name',
        'category',
        'calculation_type',
        'amount',
        'is_paid',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category' => BudgetCategory::class,
            'calculation_type' => BudgetCalculationType::class,
            'amount' => 'decimal:2',
            'is_paid' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }

    public function lineTotal(int $guestCount): string
    {
        if ($this->calculation_type === BudgetCalculationType::PerPerson) {
            return bcmul((string) $this->amount, (string) max(0, $guestCount), 2);
        }

        return number_format((float) $this->amount, 2, '.', '');
    }
}

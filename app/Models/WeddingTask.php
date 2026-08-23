<?php

namespace App\Models;

use App\Support\WeddingTaskCatalog;
use App\WeddingTaskPeriod;
use App\WeddingTaskPriority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;

class WeddingTask extends Model
{
    protected $fillable = [
        'wedding_event_id',
        'system_key',
        'title',
        'notes',
        'period',
        'priority',
        'due_date',
        'completed_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'period' => WeddingTaskPeriod::class,
            'priority' => WeddingTaskPriority::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }

    public function isSystem(): bool
    {
        return filled($this->system_key);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isOverdue(): bool
    {
        if ($this->isCompleted() || $this->due_date === null) {
            return false;
        }

        return $this->due_date->copy()->endOfDay()->isPast();
    }

    public function displayTitle(): string
    {
        if ($this->isSystem()) {
            return __('checklist.tasks.'.$this->system_key.'.title');
        }

        return (string) $this->title;
    }

    public function displayDescription(): ?string
    {
        if ($this->isSystem()) {
            $description = __('checklist.tasks.'.$this->system_key.'.description');

            return $description === 'checklist.tasks.'.$this->system_key.'.description'
                ? null
                : $description;
        }

        return filled($this->notes) ? $this->notes : null;
    }

    public function actionUrl(): ?string
    {
        $definition = $this->catalogDefinition();
        $route = $definition['action_route'] ?? null;

        if (! is_string($route) || $route === '' || ! Route::has($route)) {
            return null;
        }

        return route($route);
    }

    public function actionLabel(): ?string
    {
        $url = $this->actionUrl();

        if ($url === null) {
            return null;
        }

        $definition = $this->catalogDefinition();
        $route = $definition['action_route'] ?? null;

        return match ($route) {
            'dashboard.guests' => __('dashboard.nav.guests'),
            'dashboard.seating' => __('dashboard.nav.seating'),
            'dashboard.budget' => __('dashboard.nav.budget'),
            'dashboard.menus' => __('dashboard.nav.menus'),
            'dashboard.schedule' => __('dashboard.nav.schedule'),
            'dashboard.locations' => __('dashboard.nav.locations'),
            'dashboard.photos' => __('dashboard.nav.photos'),
            'dashboard.wedding' => __('dashboard.nav.wedding'),
            'dashboard.pushes' => __('dashboard.nav.pushes'),
            default => __('checklist.open_action'),
        };
    }

    /**
     * @return array{
     *     key: string,
     *     period: WeddingTaskPeriod,
     *     priority: WeddingTaskPriority,
     *     due_offset_days: int,
     *     sort_order: int,
     *     progress: ?string,
     *     action_route: ?string
     * }|null
     */
    public function catalogDefinition(): ?array
    {
        if (! $this->isSystem()) {
            return null;
        }

        return WeddingTaskCatalog::definition((string) $this->system_key);
    }
}

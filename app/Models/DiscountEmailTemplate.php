<?php

namespace App\Models;

use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountEmailTemplate extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'subjects',
        'bodies',
        'is_active',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subjects' => 'array',
            'bodies' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(DiscountEmailCampaign::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function subjectFor(?string $locale): string
    {
        return $this->localizedText($this->subjects ?? [], $locale);
    }

    public function bodyFor(?string $locale): string
    {
        return $this->localizedText($this->bodies ?? [], $locale);
    }

    /**
     * @param  array<string, string|null>  $map
     */
    protected function localizedText(array $map, ?string $locale): string
    {
        $resolved = Locale::resolve($locale);

        foreach ([$resolved, Locale::default(), config('app.fallback_locale', 'en'), 'en'] as $candidate) {
            $value = $map[$candidate] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        $first = collect($map)->first(fn ($value) => is_string($value) && trim($value) !== '');

        return is_string($first) ? $first : '';
    }

    public function isInUse(): bool
    {
        return $this->campaigns()->exists();
    }
}

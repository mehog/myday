<?php

namespace App\Services;

use App\DiscountEmailAudience;
use App\Models\DiscountEmailCampaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DiscountCampaignAudienceResolver
{
    /**
     * @return Collection<int, User>
     */
    public function resolve(DiscountEmailCampaign $campaign): Collection
    {
        return $this->query($campaign)->orderBy('id')->get();
    }

    /**
     * @return Builder<User>
     */
    public function query(DiscountEmailCampaign $campaign): Builder
    {
        $query = User::query()->where('is_admin', false);

        return match ($campaign->audience) {
            DiscountEmailAudience::UnpaidVerified => $query
                ->whereNotNull('email_verified_at')
                ->whereHas('weddingEvent', fn (Builder $q) => $q
                    ->whereNull('plan_tier')
                    ->where('is_demo', false)),
            DiscountEmailAudience::Unverified => $query->whereNull('email_verified_at'),
            DiscountEmailAudience::Paid => $query
                ->whereNotNull('email_verified_at')
                ->whereHas('weddingEvent', fn (Builder $q) => $q->whereNotNull('plan_tier')),
            DiscountEmailAudience::Manual => $this->manualQuery($query, $campaign->user_ids ?? []),
            DiscountEmailAudience::All => $query,
        };
    }

    public function count(DiscountEmailCampaign $campaign): int
    {
        return $this->query($campaign)->count();
    }

    /**
     * @param  Builder<User>  $query
     * @param  list<int|string>|null  $userIds
     * @return Builder<User>
     */
    protected function manualQuery(Builder $query, ?array $userIds): Builder
    {
        $ids = collect($userIds ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            throw new InvalidArgumentException('Manual audience requires at least one user.');
        }

        return $query->whereIn('id', $ids);
    }
}

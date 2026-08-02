<?php

namespace App\Models;

use App\DiscountEmailAudience;
use App\DiscountEmailCampaignStatus;
use App\DiscountEmailRecipientStatus;
use App\Support\DiscountEmailPlaceholders;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountEmailCampaign extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'discount_code_id',
        'discount_email_template_id',
        'subject',
        'body',
        'audience',
        'user_ids',
        'send_locale',
        'status',
        'created_by',
        'sent_at',
        'previewed_at',
        'preview_email',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'audience' => DiscountEmailAudience::class,
            'status' => DiscountEmailCampaignStatus::class,
            'user_ids' => 'array',
            'sent_at' => 'datetime',
            'previewed_at' => 'datetime',
        ];
    }

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DiscountEmailTemplate::class, 'discount_email_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolvedSubject(?string $locale): string
    {
        if ($this->template !== null) {
            return $this->template->subjectFor($locale);
        }

        return (string) ($this->subject ?? '');
    }

    public function resolvedBody(?string $locale): string
    {
        if ($this->template !== null) {
            return $this->template->bodyFor($locale);
        }

        return (string) ($this->body ?? '');
    }

    public function previewLocale(): string
    {
        return Locale::resolve($this->send_locale ?? Locale::default());
    }

    public function renderedSubject(?string $locale = null, ?string $sampleName = null): string
    {
        $this->loadMissing(['template', 'discountCode']);

        $locale ??= $this->previewLocale();
        $replacements = DiscountEmailPlaceholders::for(
            $this->discountCode,
            $sampleName ?? (auth()->user()?->name ?: 'Ana'),
            $locale,
        );

        return DiscountEmailPlaceholders::apply($this->resolvedSubject($locale), $replacements);
    }

    public function renderedBody(?string $locale = null, ?string $sampleName = null): string
    {
        $this->loadMissing(['template', 'discountCode']);

        $locale ??= $this->previewLocale();
        $replacements = DiscountEmailPlaceholders::for(
            $this->discountCode,
            $sampleName ?? (auth()->user()?->name ?: 'Ana'),
            $locale,
        );

        return DiscountEmailPlaceholders::apply($this->resolvedBody($locale), $replacements);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(DiscountEmailRecipient::class, 'campaign_id');
    }

    public function sentRecipientsCount(): int
    {
        return $this->recipients()
            ->where('status', DiscountEmailRecipientStatus::Sent)
            ->count();
    }

    public function failedRecipientsCount(): int
    {
        return $this->recipients()
            ->where('status', DiscountEmailRecipientStatus::Failed)
            ->count();
    }

    public function canSend(): bool
    {
        return in_array($this->status, [
            DiscountEmailCampaignStatus::Draft,
            DiscountEmailCampaignStatus::Failed,
        ], true);
    }

    public function canResendFailed(): bool
    {
        return $this->status === DiscountEmailCampaignStatus::Sent
            && $this->failedRecipientsCount() > 0;
    }
}

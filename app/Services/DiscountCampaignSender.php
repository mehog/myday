<?php

namespace App\Services;

use App\DiscountEmailCampaignStatus;
use App\DiscountEmailRecipientStatus;
use App\Jobs\SendDiscountCampaignEmailsJob;
use App\Models\DiscountEmailCampaign;
use App\Models\DiscountEmailRecipient;
use App\Models\User;
use App\Notifications\DiscountCodeEmailNotification;
use App\Support\Locale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use RuntimeException;

class DiscountCampaignSender
{
    public function __construct(
        protected DiscountCampaignAudienceResolver $audienceResolver,
    ) {}

    public function audienceCount(DiscountEmailCampaign $campaign): int
    {
        return $this->audienceResolver->count($campaign);
    }

    public function materializeRecipients(DiscountEmailCampaign $campaign): int
    {
        $users = $this->audienceResolver->resolve($campaign);

        if ($users->isEmpty()) {
            throw new RuntimeException('No recipients match this campaign audience.');
        }

        DB::transaction(function () use ($campaign, $users): void {
            foreach ($users as $user) {
                DiscountEmailRecipient::query()->firstOrCreate(
                    [
                        'campaign_id' => $campaign->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'email' => $user->email,
                        'status' => DiscountEmailRecipientStatus::Pending,
                    ],
                );
            }
        });

        return $users->count();
    }

    public function preview(DiscountEmailCampaign $campaign, string $email, ?string $locale = null): void
    {
        $campaign->loadMissing(['discountCode', 'template']);

        if ($campaign->template === null) {
            throw new RuntimeException('Campaign must have an email template.');
        }

        $resolvedLocale = Locale::resolve(
            $locale
                ?? $campaign->send_locale
                ?? (auth()->user() instanceof User ? auth()->user()->preferredLocale() : null)
        );

        Notification::route('mail', $email)->notifyNow(
            new DiscountCodeEmailNotification(
                $campaign,
                $campaign->discountCode,
                $resolvedLocale,
            )
        );

        $campaign->update([
            'previewed_at' => now(),
            'preview_email' => $email,
        ]);
    }

    public function send(DiscountEmailCampaign $campaign, bool $requirePreview = true): void
    {
        if (! $campaign->canSend()) {
            throw new RuntimeException('Campaign cannot be sent in its current status.');
        }

        if ($requirePreview && $campaign->previewed_at === null) {
            throw new RuntimeException('Preview this campaign before sending.');
        }

        $campaign->loadMissing(['discountCode', 'template']);

        if ($campaign->discountCode !== null && ! $campaign->discountCode->is_active) {
            throw new RuntimeException('Discount code must be active before sending.');
        }

        if ($campaign->template === null || ! $campaign->template->is_active) {
            throw new RuntimeException('Campaign must have an active email template.');
        }

        $this->materializeRecipients($campaign);

        $campaign->recipients()
            ->whereIn('status', [
                DiscountEmailRecipientStatus::Pending,
                DiscountEmailRecipientStatus::Failed,
            ])
            ->update([
                'status' => DiscountEmailRecipientStatus::Pending,
                'error' => null,
            ]);

        $snapshotLocale = Locale::resolve($campaign->send_locale ?? Locale::default());

        $campaign->update([
            'status' => DiscountEmailCampaignStatus::Sending,
            'subject' => $campaign->template->subjectFor($snapshotLocale),
            'body' => $campaign->template->bodyFor($snapshotLocale),
        ]);

        SendDiscountCampaignEmailsJob::dispatch($campaign->id, failedOnly: false);
    }

    public function resendFailed(DiscountEmailCampaign $campaign): void
    {
        if (! $campaign->canResendFailed()) {
            throw new InvalidArgumentException('There are no failed recipients to resend.');
        }

        $campaign->recipients()
            ->where('status', DiscountEmailRecipientStatus::Failed)
            ->update([
                'error' => null,
            ]);

        $campaign->update([
            'status' => DiscountEmailCampaignStatus::Sending,
        ]);

        SendDiscountCampaignEmailsJob::dispatch($campaign->id, failedOnly: true);
    }
}

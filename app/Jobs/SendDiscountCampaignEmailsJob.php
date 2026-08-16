<?php

namespace App\Jobs;

use App\DiscountEmailCampaignStatus;
use App\DiscountEmailRecipientStatus;
use App\Models\DiscountEmailCampaign;
use App\Models\DiscountEmailRecipient;
use App\Notifications\DiscountCodeEmailNotification;
use App\Support\Locale;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendDiscountCampaignEmailsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public int $campaignId,
        public bool $failedOnly = false,
    ) {}

    public function handle(): void
    {
        $campaign = DiscountEmailCampaign::query()
            ->with(['discountCode', 'template'])
            ->findOrFail($this->campaignId);

        $campaign->update([
            'status' => DiscountEmailCampaignStatus::Sending,
        ]);

        $query = $campaign->recipients()->with('user');

        if ($this->failedOnly) {
            $query->where('status', DiscountEmailRecipientStatus::Failed);
        } else {
            $query->where('status', DiscountEmailRecipientStatus::Pending);
        }

        $hadFailure = false;

        foreach ($query->orderBy('id')->cursor() as $recipient) {
            /** @var DiscountEmailRecipient $recipient */
            if ($recipient->user === null) {
                $recipient->update([
                    'status' => DiscountEmailRecipientStatus::Skipped,
                    'error' => 'User missing',
                ]);

                continue;
            }

            if ($recipient->user->ownsDemoInvitation()) {
                $recipient->update([
                    'status' => DiscountEmailRecipientStatus::Skipped,
                    'error' => 'Demo invitation',
                ]);

                continue;
            }

            $locale = Locale::resolve(
                $campaign->send_locale ?? $recipient->user->preferredLocale()
            );

            try {
                $recipient->user->notifyNow(new DiscountCodeEmailNotification(
                    $campaign,
                    $campaign->discountCode,
                    $locale,
                ));

                $recipient->update([
                    'status' => DiscountEmailRecipientStatus::Sent,
                    'locale' => $locale,
                    'sent_at' => now(),
                    'error' => null,
                ]);
            } catch (Throwable $e) {
                $hadFailure = true;
                $recipient->update([
                    'status' => DiscountEmailRecipientStatus::Failed,
                    'locale' => $locale,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $campaign->update([
            'status' => $hadFailure && $campaign->sentRecipientsCount() === 0
                ? DiscountEmailCampaignStatus::Failed
                : DiscountEmailCampaignStatus::Sent,
            'sent_at' => $campaign->sent_at ?? now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        DiscountEmailCampaign::query()
            ->whereKey($this->campaignId)
            ->where('status', DiscountEmailCampaignStatus::Sending)
            ->update([
                'status' => DiscountEmailCampaignStatus::Failed,
            ]);
    }
}

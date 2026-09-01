<?php

namespace App\Notifications;

use App\Filament\App\Pages\PricingPage;
use App\Models\DiscountCode;
use App\Models\DiscountEmailCampaign;
use App\Models\User;
use App\Notifications\Concerns\BuildsProductMail;
use App\Support\DiscountEmailPlaceholders;
use App\Support\Locale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscountCodeEmailNotification extends Notification implements ShouldQueue
{
    use BuildsProductMail;
    use Queueable;

    public function __construct(
        public DiscountEmailCampaign $campaign,
        public ?DiscountCode $discountCode,
        ?string $locale = null,
    ) {
        if ($locale !== null) {
            $this->locale($locale);
        }
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = Locale::resolve($this->locale ?? (
            $notifiable instanceof User ? $notifiable->preferredLocale() : null
        ));

        Locale::apply($locale);

        $this->campaign->loadMissing('template');

        $name = $notifiable instanceof User ? $notifiable->name : '';
        $replacements = DiscountEmailPlaceholders::for($this->discountCode, $name, $locale);

        $subject = DiscountEmailPlaceholders::apply(
            $this->campaign->resolvedSubject($locale),
            $replacements,
        );
        $body = DiscountEmailPlaceholders::apply(
            $this->campaign->resolvedBody($locale),
            $replacements,
        );

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting(__('notifications.discount_email_greeting', ['name' => $name]));

        foreach (preg_split("/\r\n|\n|\r/", $body) ?: [] as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $message->line($trimmed);
            }
        }

        if ($this->discountCode !== null) {
            $message->line(__('notifications.discount_email_code_line', [
                'code' => $this->discountCode->code,
            ]));
        }

        return $this->withUnsubscribeLink(
            $message->action(
                __('notifications.discount_email_action'),
                PricingPage::getUrl(panel: 'app'),
            )
        );
    }
}

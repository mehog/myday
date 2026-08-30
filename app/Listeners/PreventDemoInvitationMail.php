<?php

namespace App\Listeners;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;

class PreventDemoInvitationMail
{
    /**
     * Cancel any outbound mail addressed to a demo/marketing invitation owner or guest.
     */
    public function handle(MessageSending $event): ?bool
    {
        $emails = $this->recipientEmails($event);

        if ($emails === []) {
            return null;
        }

        if ($this->targetsSuppressedInvitation($emails)) {
            return false;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function recipientEmails(MessageSending $event): array
    {
        $message = $event->message;

        $addresses = [
            ...$message->getTo(),
            ...$message->getCc(),
            ...$message->getBcc(),
        ];

        return collect($addresses)
            ->map(function (mixed $address): ?string {
                if ($address instanceof Address) {
                    return strtolower($address->getAddress());
                }

                if (is_string($address) && $address !== '') {
                    return strtolower($address);
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $emails
     */
    private function targetsSuppressedInvitation(array $emails): bool
    {
        if (Guest::query()
            ->whereIn('email', $emails)
            ->whereHas('weddingEvent', fn ($query) => $query->suppressingOutboundMail())
            ->exists()) {
            return true;
        }

        return User::query()
            ->whereIn('email', $emails)
            ->whereHas('weddingEvent', fn ($query) => $query->suppressingOutboundMail())
            ->exists();
    }
}

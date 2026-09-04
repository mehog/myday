<?php

namespace App\Services;

use App\Models\User;
use App\Models\WeddingEvent;
use App\Models\WeddingMember;
use App\Models\WeddingPartnerInvite;
use App\Notifications\PartnerInviteNotification;
use App\WeddingMemberRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class WeddingPartnerInviteService
{
    public function createOrRefreshInvite(
        WeddingEvent $wedding,
        User $inviter,
        ?string $email = null,
    ): WeddingPartnerInvite {
        $this->assertCanManagePartners($wedding, $inviter);

        if ($wedding->hasPartner()) {
            throw ValidationException::withMessages([
                'partner' => [__('dashboard.partner_already_joined')],
            ]);
        }

        $normalizedEmail = filled($email) ? strtolower(trim($email)) : null;

        $wedding->partnerInvites()->pending()->delete();

        $invite = $wedding->partnerInvites()->create([
            'invited_by_user_id' => $inviter->id,
            'email' => $normalizedEmail,
        ]);

        return $invite->fresh();
    }

    public function sendInviteEmail(WeddingPartnerInvite $invite): void
    {
        if (! filled($invite->email)) {
            throw ValidationException::withMessages([
                'partner_email' => [__('dashboard.partner_email_required')],
            ]);
        }

        Notification::route('mail', $invite->email)
            ->notify(new PartnerInviteNotification($invite));
    }

    public function revokeInvite(WeddingPartnerInvite $invite, User $actor): void
    {
        $this->assertCanManagePartners($invite->weddingEvent, $actor);

        if (! $invite->isPending()) {
            throw ValidationException::withMessages([
                'partner' => [__('dashboard.partner_invite_not_pending')],
            ]);
        }

        $invite->delete();
    }

    public function acceptInvite(WeddingPartnerInvite $invite, User $user): void
    {
        if (! $invite->isUsable()) {
            throw ValidationException::withMessages([
                'token' => [__('dashboard.partner_invite_expired')],
            ]);
        }

        $wedding = $invite->weddingEvent()->firstOrFail();

        if ($wedding->hasPartner()) {
            throw ValidationException::withMessages([
                'token' => [__('dashboard.partner_already_joined')],
            ]);
        }

        if (! $user->canJoinWedding()) {
            throw ValidationException::withMessages([
                'email' => [__('dashboard.partner_already_has_wedding')],
            ]);
        }

        if (filled($invite->email) && strcasecmp($invite->email, $user->email) !== 0) {
            throw ValidationException::withMessages([
                'email' => [__('dashboard.partner_invite_email_mismatch')],
            ]);
        }

        DB::transaction(function () use ($invite, $user, $wedding): void {
            WeddingMember::query()->create([
                'wedding_event_id' => $wedding->id,
                'user_id' => $user->id,
                'role' => WeddingMemberRole::Partner,
            ]);

            $invite->update([
                'accepted_at' => now(),
                'accepted_by_user_id' => $user->id,
            ]);

            $wedding->partnerInvites()
                ->pending()
                ->whereKeyNot($invite->id)
                ->delete();
        });
    }

    public function removePartner(WeddingEvent $wedding, User $actor): void
    {
        $this->assertCanManagePartners($wedding, $actor);

        if (! $wedding->hasPartner()) {
            throw ValidationException::withMessages([
                'partner' => [__('dashboard.partner_not_found')],
            ]);
        }

        $wedding->members()->delete();
    }

    public function leaveWedding(User $user): void
    {
        $membership = $user->weddingMembership;

        if ($membership === null) {
            throw ValidationException::withMessages([
                'partner' => [__('dashboard.partner_not_member')],
            ]);
        }

        $membership->delete();
    }

    private function assertCanManagePartners(WeddingEvent $wedding, User $user): void
    {
        if (! $wedding->isAccessibleBy($user)) {
            throw ValidationException::withMessages([
                'partner' => [__('dashboard.partner_manage_forbidden')],
            ]);
        }
    }
}

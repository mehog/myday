<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Models\WeddingPartnerInvite;
use App\Services\WeddingPartnerInviteService;
use Livewire\Component;

class PlanningPartner extends Component
{
    use RendersDashboard;

    public string $partner_email = '';

    public ?string $partnerMessage = null;

    public function mount(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $pendingInvite = $wedding->pendingPartnerInvite;
        $this->partner_email = $pendingInvite?->email ?? '';
    }

    public function render()
    {
        $wedding = $this->wedding();

        if ($wedding instanceof WeddingEvent) {
            $wedding->load(['partner', 'pendingPartnerInvite']);
        }

        return $this->dashboardView('livewire.dashboard.planning-partner', [
            'wedding' => $wedding,
            'partner' => $wedding?->partner,
            'pendingPartnerInvite' => $wedding?->pendingPartnerInvite,
            'isOwner' => $wedding !== null && auth()->user() instanceof User && $wedding->isOwnedBy(auth()->user()),
            'isPartner' => $wedding !== null && auth()->user() instanceof User && auth()->user()->isPartnerOf($wedding),
        ], __('dashboard.partner_section_title'), [
            ['label' => __('dashboard.nav.partner'), 'url' => null],
        ], backUrl: route('dashboard.more'));
    }

    protected function wedding(): ?WeddingEvent
    {
        return auth()->user()?->accessibleWedding();
    }

    public function sendPartnerInvite(WeddingPartnerInviteService $service): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $data = $this->validate([
            'partner_email' => ['required', 'email', 'max:255'],
        ]);

        $invite = $service->createOrRefreshInvite($wedding, auth()->user(), $data['partner_email']);
        $service->sendInviteEmail($invite);

        $this->partnerMessage = __('dashboard.partner_invite_sent');
        $this->partner_email = $invite->email ?? '';
    }

    public function ensurePartnerInviteLink(WeddingPartnerInviteService $service): string
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $invite = $wedding->pendingPartnerInvite;

        if (! $invite instanceof WeddingPartnerInvite || ! $invite->isUsable()) {
            $invite = $service->createOrRefreshInvite(
                $wedding,
                auth()->user(),
                filled($this->partner_email) ? $this->partner_email : null,
            );
        }

        return $invite->acceptUrl();
    }

    public function resendPartnerInvite(WeddingPartnerInviteService $service): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $invite = $wedding->pendingPartnerInvite;

        if (! $invite instanceof WeddingPartnerInvite) {
            $this->addError('partner_email', __('dashboard.partner_invite_not_pending'));

            return;
        }

        if (filled($invite->email)) {
            $service->sendInviteEmail($invite);
            $this->partnerMessage = __('dashboard.partner_invite_resent');
        }
    }

    public function revokePartnerInvite(WeddingPartnerInviteService $service): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $invite = $wedding->pendingPartnerInvite;

        if ($invite instanceof WeddingPartnerInvite) {
            $service->revokeInvite($invite, auth()->user());
        }

        $this->partner_email = '';
        $this->partnerMessage = __('dashboard.partner_invite_cancelled');
    }

    public function removePartner(WeddingPartnerInviteService $service): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $service->removePartner($wedding, auth()->user());
        $this->partnerMessage = __('dashboard.partner_removed');
    }

    public function leaveWedding(WeddingPartnerInviteService $service): void
    {
        $service->leaveWedding(auth()->user());
        $this->redirectRoute('dashboard');
    }
}

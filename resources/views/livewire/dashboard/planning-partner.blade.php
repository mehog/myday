@php
    $controlClass = 'block w-full rounded-md border border-border bg-background px-3 py-2 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if (! $wedding)
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.no_wedding') }}</p>
        </x-dashboard.card>
    @else
        <x-dashboard.card>
            <p class="mb-4 text-sm text-muted-foreground">{{ __('dashboard.partner_section_help') }}</p>

            @if ($partnerMessage)
                <div class="mb-4 rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $partnerMessage }}</div>
            @endif

            @if ($partner)
                <div class="space-y-3">
                    <p class="text-sm"><span class="font-medium">{{ $partner->name }}</span> · {{ $partner->email }}</p>
                    <div class="flex flex-wrap gap-2">
                        @if ($isOwner)
                            <x-dashboard.button type="button" variant="secondary" wire:click="removePartner" wire:confirm="{{ __('dashboard.partner_remove_confirm') }}">
                                {{ __('dashboard.partner_remove') }}
                            </x-dashboard.button>
                        @elseif ($isPartner)
                            <x-dashboard.button type="button" variant="secondary" wire:click="leaveWedding" wire:confirm="{{ __('dashboard.partner_leave_confirm') }}">
                                {{ __('dashboard.partner_leave') }}
                            </x-dashboard.button>
                        @endif
                    </div>
                </div>
            @elseif ($pendingPartnerInvite)
                <div class="space-y-4">
                    @if ($pendingPartnerInvite->email)
                        <p class="text-sm text-muted-foreground">{{ __('dashboard.partner_invite_pending_email', ['email' => $pendingPartnerInvite->email]) }}</p>
                    @endif
                    <p class="text-sm text-muted-foreground">{{ __('dashboard.partner_invite_expires', ['date' => $pendingPartnerInvite->expires_at->format('d.m.Y H:i')]) }}</p>
                    <div
                        class="flex flex-wrap gap-2"
                        x-data="{ copied: false, async copyLink() { const url = await $wire.ensurePartnerInviteLink(); await navigator.clipboard.writeText(url); this.copied = true; setTimeout(() => this.copied = false, 2000); } }"
                    >
                        <x-dashboard.button type="button" variant="secondary" x-on:click="copyLink()">
                            <span x-text="copied ? @js(__('dashboard.partner_link_copied')) : @js(__('dashboard.partner_copy_link'))"></span>
                        </x-dashboard.button>
                        @if ($pendingPartnerInvite->email)
                            <x-dashboard.button type="button" variant="secondary" wire:click="resendPartnerInvite">{{ __('dashboard.partner_resend') }}</x-dashboard.button>
                        @endif
                        <x-dashboard.button type="button" variant="secondary" wire:click="revokePartnerInvite">{{ __('dashboard.partner_cancel_invite') }}</x-dashboard.button>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('dashboard.partner_email_label') }}</label>
                        <input type="email" wire:model="partner_email" class="{{ $controlClass }} h-10" placeholder="{{ __('dashboard.partner_email_placeholder') }}">
                        @error('partner_email') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-dashboard.button type="button" wire:click="sendPartnerInvite">{{ __('dashboard.partner_send_invite') }}</x-dashboard.button>
                        <x-dashboard.button
                            type="button"
                            variant="secondary"
                            x-data="{ copied: false, async copyLink() { const url = await $wire.ensurePartnerInviteLink(); await navigator.clipboard.writeText(url); this.copied = true; setTimeout(() => this.copied = false, 2000); } }"
                            x-on:click="copyLink()"
                        >
                            <span x-text="copied ? @js(__('dashboard.partner_link_copied')) : @js(__('dashboard.partner_copy_link'))"></span>
                        </x-dashboard.button>
                    </div>
                </div>
            @endif
        </x-dashboard.card>
    @endif
</div>

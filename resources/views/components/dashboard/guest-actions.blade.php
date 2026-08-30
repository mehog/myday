@props(['guest', 'locked' => false, 'compact' => false])

<div
    class="relative"
    x-data="{
        open: false,
        menuStyle: '',
        toggle() {
            this.open = ! this.open;
            if (this.open) {
                this.place();
                this.$nextTick(() => this.place());
            }
        },
        place() {
            const button = this.$refs.trigger;
            const menu = this.$refs.menu;
            if (! button) {
                return;
            }

            const rect = button.getBoundingClientRect();
            const menuWidth = menu?.offsetWidth || 208;
            const menuHeight = menu?.offsetHeight || 280;
            const gap = 4;
            const margin = 8;
            let top = rect.bottom + gap;

            if (top + menuHeight > window.innerHeight - margin) {
                top = Math.max(margin, rect.top - gap - menuHeight);
            }

            let left = rect.right - menuWidth;
            left = Math.min(left, window.innerWidth - menuWidth - margin);
            left = Math.max(margin, left);

            this.menuStyle = `position:fixed;top:${top}px;left:${left}px;`;
        },
        closeIfOutside(event) {
            if (this.$refs.trigger?.contains(event.target)) {
                return;
            }
            this.open = false;
        },
        init() {
            this._reposition = () => {
                if (this.open) {
                    this.place();
                }
            };
            this._close = () => {
                this.open = false;
            };
            window.addEventListener('resize', this._reposition);
            document.querySelector('.dashboard-main')?.addEventListener('scroll', this._close, { passive: true });
        },
        destroy() {
            window.removeEventListener('resize', this._reposition);
            document.querySelector('.dashboard-main')?.removeEventListener('scroll', this._close);
        },
    }"
>
    <button
        type="button"
        x-ref="trigger"
        @class([
            'inline-flex items-center justify-center transition-colors',
            'h-9 w-9 rounded-full text-muted-foreground hover:bg-accent hover:text-foreground' => $compact,
            'gap-1 rounded-md border border-border px-2.5 py-1.5 text-xs' => ! $compact,
        ])
        @click="toggle()"
        aria-label="{{ __('guests.more_actions') }}"
        :aria-expanded="open"
    >
        @if ($compact)
            <x-dashboard.icon name="ellipsis" class="h-5 w-5" />
        @else
            {{ __('guests.more_actions') }}
            <x-dashboard.icon name="chevron-down" class="h-3 w-3" />
        @endif
    </button>
    <template x-teleport="body">
        <div
            x-ref="menu"
            x-show="open"
            x-on:click.outside="closeIfOutside($event)"
            x-cloak
            :style="menuStyle"
            class="fixed z-50 w-52 rounded-xl border border-border bg-popover p-1 shadow-lg"
        >
            <button type="button" class="inline-flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs hover:bg-accent"
                x-on:click="navigator.clipboard.writeText(@js($guest->personal_url)); open = false">
                <x-dashboard.icon name="link" class="h-3.5 w-3.5 shrink-0" />
                {{ __('guests.copy_link') }}
            </button>
            @if (! $locked)
                @if ($guest->rsvp_status === null)
                    <button type="button" class="inline-flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs hover:bg-accent" wire:click="openSendInvite({{ $guest->id }})" @click="open = false">
                        <x-dashboard.icon name="send" class="h-3.5 w-3.5 shrink-0" />
                        {{ __('guests.send_invite') }}
                    </button>
                @endif
                <button type="button" class="inline-flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs hover:bg-accent" wire:click="openMarkSent({{ $guest->id }})" @click="open = false">
                    <x-dashboard.icon name="send" class="h-3.5 w-3.5 shrink-0" />
                    {{ __('guests.mark_sent') }}
                </button>
                <button type="button" class="inline-flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs hover:bg-accent" wire:click="openMarkRsvp({{ $guest->id }})" @click="open = false">
                    <x-dashboard.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                    {{ __('guests.mark_rsvp') }}
                </button>
                @if (filled($guest->plus_one_name))
                    <button type="button" class="inline-flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs hover:bg-accent" wire:click="openSeatingName({{ $guest->id }})" @click="open = false">
                        <x-dashboard.icon name="table" class="h-3.5 w-3.5 shrink-0" />
                        {{ __('guests.seating_name') }}
                    </button>
                @endif
                <button type="button" class="inline-flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs hover:bg-accent" wire:click="openChildren({{ $guest->id }})" @click="open = false">
                    <x-dashboard.icon name="users" class="h-3.5 w-3.5 shrink-0" />
                    {{ __('guests.children') }}
                </button>
                <button type="button" class="inline-flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs hover:bg-accent" wire:click="openEdit({{ $guest->id }})" @click="open = false">
                    <x-dashboard.icon name="pencil" class="h-3.5 w-3.5 shrink-0" />
                    {{ __('dashboard.edit') }}
                </button>
                <button type="button" class="inline-flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs text-red-600 hover:bg-accent" wire:click="deleteGuest({{ $guest->id }})" wire:confirm="{{ __('dashboard.guests_delete_confirm') }}" @click="open = false">
                    <x-dashboard.icon name="trash" class="h-3.5 w-3.5 shrink-0" />
                    {{ __('dashboard.delete') }}
                </button>
            @endif
        </div>
    </template>
</div>

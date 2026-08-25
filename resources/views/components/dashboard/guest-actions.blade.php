@props(['guest', 'locked' => false])

<div class="relative" x-data="{ open: false }">
    <button type="button" class="inline-flex items-center gap-1 rounded-md border border-border px-2.5 py-1.5 text-xs" @click="open = !open">
        {{ __('guests.more_actions') }}
        <x-dashboard.icon name="chevron-down" class="h-3 w-3" />
    </button>
    <div
        x-show="open"
        @click.outside="open = false"
        x-cloak
        class="absolute right-0 z-20 mt-1 w-52 rounded-md border border-border bg-popover p-1 shadow-lg"
    >
        <button type="button" class="inline-flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-left text-xs hover:bg-accent"
            x-on:click="navigator.clipboard.writeText(@js($guest->personal_url)); open = false">
            <x-dashboard.icon name="link" class="h-3.5 w-3.5 shrink-0" />
            {{ __('guests.copy_link') }}
        </button>
        @if (! $locked)
            <button type="button" class="inline-flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-left text-xs hover:bg-accent" wire:click="openMarkSent({{ $guest->id }})" @click="open = false">
                <x-dashboard.icon name="send" class="h-3.5 w-3.5 shrink-0" />
                {{ __('guests.mark_sent') }}
            </button>
            <button type="button" class="inline-flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-left text-xs hover:bg-accent" wire:click="openMarkRsvp({{ $guest->id }})" @click="open = false">
                <x-dashboard.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                {{ __('guests.mark_rsvp') }}
            </button>
            @if (filled($guest->plus_one_name))
                <button type="button" class="inline-flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-left text-xs hover:bg-accent" wire:click="openSeatingName({{ $guest->id }})" @click="open = false">
                    <x-dashboard.icon name="table" class="h-3.5 w-3.5 shrink-0" />
                    {{ __('guests.seating_name') }}
                </button>
            @endif
            <button type="button" class="inline-flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-left text-xs hover:bg-accent" wire:click="openChildren({{ $guest->id }})" @click="open = false">
                <x-dashboard.icon name="users" class="h-3.5 w-3.5 shrink-0" />
                {{ __('guests.children') }}
            </button>
            <button type="button" class="inline-flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-left text-xs hover:bg-accent" wire:click="openEdit({{ $guest->id }})" @click="open = false">
                <x-dashboard.icon name="pencil" class="h-3.5 w-3.5 shrink-0" />
                {{ __('dashboard.edit') }}
            </button>
            <button type="button" class="inline-flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-left text-xs text-red-600 hover:bg-accent" wire:click="deleteGuest({{ $guest->id }})" wire:confirm="{{ __('dashboard.guests_delete_confirm') }}" @click="open = false">
                <x-dashboard.icon name="trash" class="h-3.5 w-3.5 shrink-0" />
                {{ __('dashboard.delete') }}
            </button>
        @endif
    </div>
</div>

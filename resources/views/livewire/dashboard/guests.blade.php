@php
    $controlClass = 'block h-10 w-full rounded-md border border-border bg-background px-3 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif
    @if ($flashError)
        <div class="rounded-lg border border-red-300/50 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100">{{ $flashError }}</div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-xl font-semibold">{{ __('dashboard.guests_title') }}</h2>
        <div class="flex flex-wrap gap-2">
            <x-dashboard.button type="button" variant="secondary" wire:click="openPlaceCards">
                {{ __('guests.place_cards_download') }}
            </x-dashboard.button>
            @if (! $locked)
                <x-dashboard.button type="button" wire:click="openCreate">
                    <x-dashboard.icon name="plus" class="h-4 w-4" />
                    {{ __('dashboard.guests_add') }}
                </x-dashboard.button>
            @endif
        </div>
    </div>

    @if (! $wedding)
        <div class="rounded-xl border border-border bg-card p-4 shadow-sm">
            <p class="text-sm text-muted-foreground">{{ __('dashboard.no_wedding') }}</p>
        </div>
    @else
        @if ($locked)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                {{ __('app.wedding_archived_readonly') }}
            </div>
        @endif

        <div class="grid gap-3 rounded-xl border border-border bg-card p-4 shadow-sm md:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-muted-foreground">{{ __('dashboard.search') }}</label>
                <input type="search" wire:model.live.debounce.300ms="search" class="{{ $controlClass }}">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-muted-foreground">{{ __('guests.field_rsvp_status') }}</label>
                <select wire:model.live="filterRsvp" class="{{ $controlClass }}">
                    <option value="">—</option>
                    <option value="pending">{{ __('guests.rsvp_pending') }}</option>
                    @foreach ($rsvpOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                @php
                    $selectedLabelNames = collect($filterLabels)
                        ->map(fn (string $value): string => $value === '__none'
                            ? __('guests.labels_none')
                            : ($labelOptions[$value] ?? $value))
                        ->filter()
                        ->values()
                        ->all();
                @endphp
                <label class="mb-1 block text-xs font-medium text-muted-foreground">{{ __('guests.filter_labels') }}</label>
                <div class="relative" x-data="{ open: false }">
                    <button
                        type="button"
                        class="{{ $controlClass }} flex items-center justify-between gap-2 text-left"
                        @click="open = !open"
                    >
                        <span class="min-w-0 truncate {{ $selectedLabelNames === [] ? 'text-muted-foreground' : '' }}">
                            {{ $selectedLabelNames === [] ? __('guests.filter_labels_all') : implode(', ', $selectedLabelNames) }}
                        </span>
                        <x-dashboard.icon name="chevron-down" class="h-4 w-4 shrink-0 text-muted-foreground" />
                    </button>
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-cloak
                        class="absolute z-20 mt-1 w-full rounded-md border border-border bg-popover p-1 shadow-lg"
                    >
                        <button
                            type="button"
                            class="block w-full rounded-sm px-2 py-1.5 text-left text-xs text-muted-foreground hover:bg-accent"
                            wire:click="clearLabelFilter"
                        >
                            {{ __('guests.filter_labels_clear') }}
                        </button>
                        <label class="flex cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent">
                            <input type="checkbox" value="__none" wire:model.live="filterLabels" class="rounded border-border">
                            {{ __('guests.labels_none') }}
                        </label>
                        @foreach ($labelOptions as $value => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent">
                                <input type="checkbox" value="{{ $value }}" wire:model.live="filterLabels" class="rounded border-border">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="overflow-x-auto">
                @if ($guests->isEmpty())
                    <div class="p-6">
                        <p class="font-medium">{{ __('guests.empty_heading') }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ __('guests.empty_description') }}</p>
                    </div>
                @else
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-border text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="cursor-pointer px-3 py-3" wire:click="setSort('name')">{{ __('guests.field_name') }}</th>
                                <th class="px-3 py-3">{{ __('guests.field_labels') }}</th>
                                <th class="cursor-pointer px-3 py-3" wire:click="setSort('rsvp_status')">{{ __('guests.field_rsvp_status') }}</th>
                                <th class="px-3 py-3">{{ __('guests.field_plus_one_name') }}</th>
                                <th class="px-3 py-3">{{ __('guests.field_children') }}</th>
                                <th class="px-3 py-3">{{ __('guests.field_menus') }}</th>
                                <th class="px-3 py-3">{{ __('guests.field_accommodation') }}</th>
                                <th class="cursor-pointer px-3 py-3" wire:click="setSort('invite_sent_at')">{{ __('guests.invite_sent') }}</th>
                                <th class="cursor-pointer px-3 py-3" wire:click="setSort('last_visited_at')">{{ __('guests.last_opened') }}</th>
                                <th class="px-3 py-3">{{ __('dashboard.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($guests as $guest)
                                <tr class="border-t border-border align-top" wire:key="guest-{{ $guest->id }}">
                                    <td class="px-3 py-3 font-medium">
                                        {{ $guest->name }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($guest->labels ?? [] as $label)
                                                <span class="rounded-full bg-muted px-2 py-0.5 text-[10px]">{{ $label->label() }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-col items-start gap-1.5">
                                            <span>
                                                {{ $guest->rsvp_status?->label() ?? __('guests.rsvp_pending') }}
                                                @if ($guest->rsvp_manual_override)
                                                    <span class="text-[10px] text-muted-foreground">({{ __('guests.rsvp_manual_flag') }})</span>
                                                @endif
                                            </span>
                                            @if (! $locked && $guest->rsvp_status === null)
                                                <x-dashboard.button type="button" class="!px-2 !py-1 text-xs" wire:click="openSendInvite({{ $guest->id }})">
                                                    {{ __('guests.send_invite') }}
                                                </x-dashboard.button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">{{ $guest->plus_one_name ?: ($guest->plus_one_allowed ? '—' : '—') }}</td>
                                    <td class="px-3 py-3">
                                        {{ $guest->children->map->displayName()->implode(', ') ?: '—' }}
                                    </td>
                                    <td class="max-w-[12rem] px-3 py-3 text-xs text-muted-foreground">{{ $this->formatGuestMenus($guest) ?: '—' }}</td>
                                    <td class="px-3 py-3">
                                        @if (($guest->accommodation_count ?? 0) > 0)
                                            {{ __('guests.accommodation_count_value', ['count' => $guest->accommodation_count]) }}
                                        @else
                                            {{ __('guests.accommodation_none') }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-xs">
                                        @if ($guest->invite_sent_at)
                                            {{ $guest->invite_platform?->label() }}<br>
                                            <span class="text-muted-foreground">{{ $guest->invite_sent_at->diffForHumans() }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-xs text-muted-foreground">
                                        {{ $guest->last_visited_at ? \Illuminate\Support\Carbon::parse($guest->last_visited_at)->diffForHumans() : '—' }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="relative" x-data="{ open: false }">
                                            <button type="button" class="inline-flex items-center gap-1 rounded-md border border-border px-2 py-1 text-xs" @click="open = !open">
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
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif

    {{-- Create / Edit --}}
    <x-dashboard.modal :show="$modal === 'form'" :title="$activeGuestId ? __('dashboard.edit') : __('dashboard.guests_add')" max-width="max-w-xl">
        <form wire:submit="saveGuest" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('guests.field_name') }}</label>
                <input type="text" wire:model="name" class="{{ $controlClass }}">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('guests.field_email') }}</label>
                    <input type="email" wire:model="email" class="{{ $controlClass }}">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('guests.field_phone') }}</label>
                    <input type="text" wire:model="phone" class="{{ $controlClass }}">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('guests.field_invitation_locale') }}</label>
                <select wire:model="invitation_locale" class="{{ $controlClass }}">
                    <option value="">{{ __('guests.field_invitation_locale_default') }}</option>
                    @foreach ($localeOptions as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="plus_one_allowed" class="rounded border-border">
                {{ __('guests.field_plus_one_allowed') }}
            </label>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('guests.field_labels') }}</label>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($labelOptions as $value => $label)
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="labels" value="{{ $value }}" class="rounded border-border">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.modal>

    {{-- Send invite --}}
    <x-dashboard.modal :show="$modal === 'send'" :title="__('guests.send_invite')" max-width="max-w-lg">
        <div class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('guests.message') }}</label>
                <textarea readonly rows="6" class="w-full rounded-md border border-border bg-muted/40 px-3 py-2 text-sm">{{ $inviteMessage }}</textarea>
            </div>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($platformOptions as $value => $label)
                    @continue($value === 'manual')
                    <x-dashboard.button type="button" variant="secondary" wire:click="sendVia('{{ $value }}')">
                        {{ $label }}
                    </x-dashboard.button>
                @endforeach
            </div>
            <div class="flex justify-end">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('guests.close') }}</x-dashboard.button>
            </div>
        </div>
    </x-dashboard.modal>

    {{-- Mark sent --}}
    <x-dashboard.modal :show="$modal === 'mark_sent'" :title="__('guests.mark_sent')">
        <form wire:submit="saveMarkSent" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('guests.platform') }}</label>
                <select wire:model="invite_platform" class="{{ $controlClass }}">
                    @foreach ($platformOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.modal>

    {{-- Mark RSVP --}}
    <x-dashboard.modal :show="$modal === 'rsvp'" :title="__('guests.mark_rsvp')" max-width="max-w-lg">
        <form wire:submit="saveMarkRsvp" class="space-y-4">
            <p class="text-sm text-muted-foreground">{{ __('guests.mark_rsvp_description') }}</p>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('guests.field_rsvp_status') }}</label>
                <select wire:model.live="rsvp_status" class="{{ $controlClass }}">
                    @foreach ($rsvpOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if ($rsvp_status === 'yes' && $activeGuest?->plus_one_allowed)
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('guests.field_plus_one_name') }}</label>
                    <input type="text" wire:model.live="plus_one_name" class="{{ $controlClass }}">
                </div>
            @endif
            @if ($rsvp_status === 'yes' && count($menuOptions) > 0)
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('guests.field_menu') }}</label>
                    <select wire:model="menu_option_id" class="{{ $controlClass }}">
                        <option value="">—</option>
                        @foreach ($menuOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($rsvp_status === 'yes' && $activeGuest?->plus_one_allowed && filled($plus_one_name) && count($menuOptions) > 0)
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('guests.field_plus_one_menu') }}</label>
                    <select wire:model="plus_one_menu_option_id" class="{{ $controlClass }}">
                        <option value="">—</option>
                        @foreach ($menuOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($rsvp_status === 'yes' && $wedding?->accommodation_enabled)
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('guests.field_accommodation_count') }}</label>
                    <input type="number" min="0" wire:model="accommodation_count" class="{{ $controlClass }}">
                </div>
            @endif
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.modal>

    {{-- Seating name --}}
    <x-dashboard.modal :show="$modal === 'seating'" :title="__('guests.seating_name')">
        <form wire:submit="saveSeatingName" class="space-y-4">
            <p class="text-sm text-muted-foreground">{{ __('guests.seating_name_description') }}</p>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('guests.field_plus_one_seating_name') }}</label>
                <input type="text" wire:model="plus_one_seating_name" class="{{ $controlClass }}">
            </div>
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.modal>

    {{-- Children --}}
    <x-dashboard.modal :show="$modal === 'children'" :title="__('guests.children')" max-width="max-w-xl">
        <form wire:submit="saveChildren" class="space-y-4">
            <p class="text-sm text-muted-foreground">{{ __('guests.children_description') }}</p>
            @foreach ($children as $index => $child)
                <div class="space-y-2 rounded-lg border border-border p-3" wire:key="child-row-{{ $index }}">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium">#{{ $index + 1 }}</p>
                        <button type="button" class="text-xs text-red-600" wire:click="removeChildRow({{ $index }})">{{ __('dashboard.delete') }}</button>
                    </div>
                    <input type="text" wire:model="children.{{ $index }}.name" placeholder="{{ __('guests.field_child_name') }}" class="{{ $controlClass }}">
                    <input type="text" wire:model="children.{{ $index }}.seating_name" placeholder="{{ __('guests.field_child_seating_name') }}" class="{{ $controlClass }}">
                    @if (count($menuOptions) > 0)
                        <select wire:model="children.{{ $index }}.menu_option_id" class="{{ $controlClass }}">
                            <option value="">{{ __('guests.field_child_menu') }}</option>
                            @foreach ($menuOptions as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            @endforeach
            <x-dashboard.button type="button" variant="secondary" wire:click="addChildRow">{{ __('guests.children_add') }}</x-dashboard.button>
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.modal>

    {{-- Place cards --}}
    <x-dashboard.modal :show="$modal === 'place_cards'" :title="__('guests.place_cards_download')" max-width="max-w-lg">
        <div class="space-y-4">
            <p class="text-sm text-muted-foreground">{{ __('guests.place_cards_modal_description') }}</p>
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium">{{ __('guests.place_cards_color_bg') }}</label>
                    <input type="color" wire:model.live="placeCardBg" class="h-10 w-full rounded border border-border">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium">{{ __('guests.place_cards_color_text') }}</label>
                    <input type="color" wire:model.live="placeCardText" class="h-10 w-full rounded border border-border">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium">{{ __('guests.place_cards_color_accent') }}</label>
                    <input type="color" wire:model.live="placeCardAccent" class="h-10 w-full rounded border border-border">
                </div>
            </div>
            <div class="rounded-lg border border-border p-3">
                <p class="mb-2 text-xs font-medium text-muted-foreground">{{ __('guests.place_cards_preview') }}</p>
                <div
                    class="rounded-lg p-4 text-center shadow-sm"
                    style="background-color: {{ $placeCardBg }}; color: {{ $placeCardText }}; border: 2px solid {{ $placeCardAccent }}"
                >
                    <p class="text-lg font-semibold">{{ __('guests.place_cards_preview_guest') }}</p>
                    <p class="mt-1 text-sm opacity-80">&amp; {{ __('guests.place_cards_preview_plus_one') }}</p>
                    <p class="mt-3 whitespace-pre-line text-xs" style="color: {{ $placeCardAccent }}">{{ __('guests.place_cards_scan_cta') }}</p>
                </div>
            </div>
            <p class="text-xs text-muted-foreground">{{ __('guests.place_cards_print_hint') }}</p>
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button :href="$this->placeCardsUrl()" target="_blank" rel="noopener">
                    {{ __('guests.place_cards_download') }}
                </x-dashboard.button>
            </div>
        </div>
    </x-dashboard.modal>
</div>

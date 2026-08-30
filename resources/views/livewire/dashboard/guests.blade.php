@php
    $controlClass = 'block h-10 w-full rounded-md border border-border bg-background px-3 text-sm disabled:opacity-60';
@endphp

<div class="space-y-5 lg:space-y-6" x-data="{ guestsMenuOpen: false }">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif
    @if ($flashError)
        <div class="rounded-lg border border-red-300/50 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100">{{ $flashError }}</div>
    @endif

    {{-- Desktop header --}}
    <div class="hidden items-center justify-between gap-3 lg:flex">
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

    {{-- Mobile overflow for place cards --}}
    @if ($wedding)
        <div class="relative flex justify-end lg:hidden">
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-card text-foreground"
                @click="guestsMenuOpen = !guestsMenuOpen"
                aria-label="{{ __('dashboard.actions') }}"
            >
                <x-dashboard.icon name="ellipsis" class="h-5 w-5" />
            </button>
            <div
                x-show="guestsMenuOpen"
                @click.outside="guestsMenuOpen = false"
                x-cloak
                class="absolute right-0 top-11 z-20 w-56 overflow-hidden rounded-xl border border-border bg-popover shadow-lg"
            >
                <button
                    type="button"
                    class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm hover:bg-accent"
                    wire:click="openPlaceCards"
                    @click="guestsMenuOpen = false"
                >
                    <x-dashboard.icon name="photo" class="h-4 w-4 shrink-0" />
                    {{ __('guests.place_cards_download') }}
                </button>
            </div>
        </div>
    @endif

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

        {{-- Mobile: search + chip filters --}}
        <div class="space-y-3 lg:hidden">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('dashboard.search') }}"
                class="{{ $controlClass }} !h-11 rounded-xl"
            >
            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none" style="scrollbar-width: none">
                <button
                    type="button"
                    wire:click="$set('filterRsvp', '')"
                    @class([
                        'shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors',
                        'bg-foreground text-background' => $filterRsvp === '',
                        'bg-muted text-muted-foreground' => $filterRsvp !== '',
                    ])
                >
                    {{ __('dashboard.guests_trash_all') }}
                </button>
                <button
                    type="button"
                    wire:click="$set('filterRsvp', 'pending')"
                    @class([
                        'shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors',
                        'bg-foreground text-background' => $filterRsvp === 'pending',
                        'bg-muted text-muted-foreground' => $filterRsvp !== 'pending',
                    ])
                >
                    {{ __('guests.rsvp_pending') }}
                </button>
                @foreach ($rsvpOptions as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('filterRsvp', '{{ $value }}')"
                        @class([
                            'shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors',
                            'bg-foreground text-background' => $filterRsvp === $value,
                            'bg-muted text-muted-foreground' => $filterRsvp !== $value,
                        ])
                    >
                        {{ $label }}
                    </button>
                @endforeach
                <div
                    class="relative shrink-0"
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
                            const menuWidth = menu?.offsetWidth || 224;
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
                        class="inline-flex items-center gap-1 rounded-full bg-muted px-3 py-1.5 text-xs font-medium text-muted-foreground"
                        @click="toggle()"
                        :aria-expanded="open"
                    >
                        {{ __('guests.filter_labels') }}
                        @if (count($filterLabels) > 0)
                            <span class="rounded-full bg-primary px-1.5 text-[10px] text-primary-foreground">{{ count($filterLabels) }}</span>
                        @endif
                        <x-dashboard.icon name="chevron-down" class="h-3 w-3" />
                    </button>
                    <template x-teleport="body">
                        <div
                            x-ref="menu"
                            x-show="open"
                            x-on:click.outside="closeIfOutside($event)"
                            x-cloak
                            :style="menuStyle"
                            class="fixed z-50 max-h-[min(70dvh,24rem)] w-56 overflow-y-auto rounded-xl border border-border bg-popover p-1 shadow-lg"
                        >
                            <button
                                type="button"
                                class="block w-full rounded-lg px-2 py-1.5 text-left text-xs text-muted-foreground hover:bg-accent"
                                wire:click="clearLabelFilter"
                            >
                                {{ __('guests.filter_labels_clear') }}
                            </button>
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-accent">
                                <input type="checkbox" value="__none" wire:model.live="filterLabels" class="rounded border-border">
                                {{ __('guests.labels_none') }}
                            </label>
                            @foreach ($labelOptions as $value => $label)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-accent">
                                    <input type="checkbox" value="{{ $value }}" wire:model.live="filterLabels" class="rounded border-border">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Desktop filters --}}
        <div class="hidden gap-3 rounded-xl border border-border bg-card p-4 shadow-sm md:grid-cols-3 lg:grid">
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

        <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm lg:rounded-xl">
            <div class="overflow-x-auto">
                @if ($guests->isEmpty())
                    <div class="p-6">
                        <p class="font-medium">{{ __('guests.empty_heading') }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ __('guests.empty_description') }}</p>
                    </div>
                @else
                    {{-- Mobile contacts list --}}
                    <div class="lg:hidden">
                        @foreach ($guests as $guest)
                            <div
                                class="dashboard-guest-row group"
                                wire:key="guest-card-{{ $guest->id }}"
                                @if (! $locked)
                                    wire:click="openGuestRowActions({{ $guest->id }})"
                                    role="button"
                                    tabindex="0"
                                @endif
                            >
                                <div class="flex min-w-0 flex-1 items-center gap-3 text-left">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/15 text-sm font-semibold text-primary">
                                        {{ strtoupper(mb_substr($guest->name, 0, 1)) }}
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-[15px] font-semibold">{{ $guest->name }}</span>
                                        <span class="mt-0.5 block truncate text-xs text-muted-foreground">
                                            {{ $guest->rsvp_status?->label() ?? __('guests.rsvp_pending') }}
                                            @if (($guest->labels ?? collect())->isNotEmpty())
                                                · {{ $guest->labels->map->label()->implode(', ') }}
                                            @endif
                                        </span>
                                    </span>
                                </div>
                                <div @click.stop>
                                    <x-dashboard.guest-actions :guest="$guest" :locked="$locked" compact />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Desktop table --}}
                    <table class="hidden min-w-full text-left text-sm lg:table">
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
                                        <x-dashboard.guest-actions :guest="$guest" :locked="$locked" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif

    @if ($wedding && ! $locked)
        <button type="button" class="dashboard-fab" wire:click="openCreate" aria-label="{{ __('dashboard.guests_add') }}">
            <x-dashboard.icon name="plus" class="h-5 w-5" />
            <span>{{ __('dashboard.guests_add') }}</span>
        </button>
    @endif

    {{-- Mobile row actions --}}
    <x-dashboard.modal
        :show="$modal === 'row_actions'"
        :title="$activeGuest?->name ?? __('dashboard.actions')"
        max-width="max-w-sm"
    >
        <div class="space-y-2">
            @if ($activeGuest)
                <x-dashboard.button
                    type="button"
                    variant="secondary"
                    class="w-full justify-start gap-2"
                    wire:click="openSendInvite({{ $activeGuest->id }})"
                >
                    <x-dashboard.icon name="send" class="h-4 w-4 shrink-0" />
                    {{ $activeGuest->invite_sent_at ? __('guests.resend_invite') : __('guests.send_invite') }}
                </x-dashboard.button>
                <x-dashboard.button
                    type="button"
                    variant="secondary"
                    class="w-full justify-start gap-2"
                    wire:click="openEdit({{ $activeGuest->id }})"
                >
                    <x-dashboard.icon name="pencil" class="h-4 w-4 shrink-0" />
                    {{ __('dashboard.edit') }}
                </x-dashboard.button>
            @endif
        </div>
    </x-dashboard.modal>

    {{-- Create / Edit --}}
    <x-dashboard.modal :show="$modal === 'form'" :title="$activeGuestId ? __('dashboard.edit') : __('dashboard.guests_add')" max-width="max-w-xl">
        <form wire:submit="saveGuest" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('guests.field_name') }}</label>
                <input type="text" wire:model="name" class="{{ $controlClass }}">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            {{-- Email/phone kept in Livewire state for existing data; hidden until we drop them fully. --}}
            <div class="hidden" aria-hidden="true">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('guests.field_email') }}</label>
                        <input type="email" wire:model="email" class="{{ $controlClass }}" tabindex="-1">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('guests.field_phone') }}</label>
                        <input type="text" wire:model="phone" class="{{ $controlClass }}" tabindex="-1">
                    </div>
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

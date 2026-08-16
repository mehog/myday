@php
    use App\RsvpStatus;

    $controlClass = 'block h-10 w-full rounded-md border border-border bg-background px-3 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif
    @if ($flashError)
        <div class="rounded-lg border border-red-300/50 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100">{{ $flashError }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold">{{ __('dashboard.guests_title') }}</h2>
        <div class="w-full max-w-xs sm:w-auto">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('dashboard.search') }}"
                class="{{ $controlClass }}"
            >
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

        @if (! $locked)
            <div class="rounded-xl border border-border bg-card p-4 shadow-sm md:p-5">
                <h3 class="mb-4 font-medium">{{ __('dashboard.guests_add') }}</h3>
                <form wire:submit="addGuest" class="grid gap-4 sm:grid-cols-12 sm:items-end">
                    <div class="sm:col-span-5">
                        <label class="mb-1 block text-sm font-medium">{{ __('dashboard.guests_name') }}</label>
                        <input type="text" wire:model="newName" class="{{ $controlClass }}" @disabled(! $canAdd)>
                        @error('newName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-4">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="newPlusOneAllowed" class="rounded border-border" @disabled(! $canAdd)>
                            {{ __('dashboard.guests_plus_one') }}
                        </label>
                    </div>
                    <div class="sm:col-span-3">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-60" @disabled(! $canAdd)>
                            {{ __('dashboard.guests_add') }}
                        </button>
                    </div>
                </form>
                @if (! $canAdd)
                    <p class="mt-3 text-sm text-muted-foreground">
                        {{ __('pricing.guest_limit_reached', ['count' => $wedding->activeGuestCount(), 'limit' => $wedding->guest_limit ?? 0]) }}
                    </p>
                @endif
            </div>
        @endif

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="p-4 md:p-5">
                @if ($guests->isEmpty())
                    <p class="text-sm text-muted-foreground">{{ __('dashboard.empty') }}</p>
                @else
                    <div class="-mx-4 overflow-x-auto md:-mx-5">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-border text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3 md:px-5">{{ __('dashboard.guests_name') }}</th>
                                    <th class="px-4 py-3">{{ __('dashboard.guests_rsvp') }}</th>
                                    <th class="px-4 py-3">{{ __('dashboard.guests_plus_one') }}</th>
                                    <th class="px-4 py-3">{{ __('dashboard.guests_link') }}</th>
                                    <th class="px-4 py-3 md:px-5">{{ __('dashboard.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($guests as $guest)
                                    <tr class="border-t border-border" wire:key="guest-{{ $guest->id }}">
                                        <td class="px-4 py-3 font-medium md:px-5">{{ $guest->name }}</td>
                                        <td class="px-4 py-3">
                                            <select
                                                class="{{ $controlClass }} max-w-[10rem]"
                                                @disabled($locked)
                                                wire:change="updateRsvp({{ $guest->id }}, $event.target.value)"
                                            >
                                                <option value="" @selected($guest->rsvp_status === null)>{{ __('guests.rsvp_pending') }}</option>
                                                @foreach (RsvpStatus::cases() as $status)
                                                    <option value="{{ $status->value }}" @selected($guest->rsvp_status === $status)>{{ $status->label() }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $guest->plus_one_allowed ? __('invitation.rsvp_yes') : '—' }}
                                            @if ($guest->plus_one_name)
                                                <span class="text-muted-foreground">({{ $guest->plus_one_name }})</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <button
                                                type="button"
                                                class="rounded-md px-2 py-1 text-xs font-medium text-muted-foreground hover:bg-accent"
                                                x-data="{ copied: false }"
                                                x-on:click="
                                                    navigator.clipboard.writeText(@js($guest->personal_url));
                                                    copied = true;
                                                    setTimeout(() => copied = false, 2000);
                                                "
                                            >
                                                <span x-text="copied ? @js(__('referrals.link_copied')) : @js(__('dashboard.guests_copy_link'))"></span>
                                            </button>
                                        </td>
                                        <td class="px-4 py-3 md:px-5">
                                            @if (! $locked)
                                                <button
                                                    type="button"
                                                    class="rounded-md bg-destructive px-2 py-1 text-xs font-medium text-destructive-foreground hover:opacity-90"
                                                    wire:click="deleteGuest({{ $guest->id }})"
                                                    wire:confirm="{{ __('dashboard.guests_delete_confirm') }}"
                                                >
                                                    {{ __('dashboard.delete') }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

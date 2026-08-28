@php
    $user = auth()->user();
@endphp

@if ($user !== null && ! $user->hasVerifiedEmail())
    <div class="mb-4 rounded-xl border border-amber-300/60 bg-amber-50 px-4 py-4 text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-50">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div class="min-w-0 space-y-1">
                <p class="text-sm font-semibold">{{ __('onboarding.verify_banner_title') }}</p>
                <p class="text-sm text-amber-900/80 dark:text-amber-100/80">
                    {{ __('onboarding.verify_banner_body', ['email' => $user->email]) }}
                </p>
                @if ($graceExpiresAt !== null)
                    <p class="text-xs text-amber-800/70 dark:text-amber-100/70">
                        {{ __('onboarding.verify_banner_grace_remaining', ['time' => $graceExpiresAt->diffForHumans()]) }}
                    </p>
                @endif
                @if ($updated)
                    <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                        {{ __('onboarding.verify_email_updated', ['email' => $user->email]) }}
                    </p>
                @elseif ($resent)
                    <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                        {{ __('onboarding.verify_sent') }}
                    </p>
                @endif
            </div>

            <div class="flex shrink-0 flex-wrap gap-2">
                <button
                    type="button"
                    wire:click="resend"
                    wire:loading.attr="disabled"
                    class="rounded-md border border-amber-400/60 bg-white px-3 py-2 text-sm font-medium text-amber-950 hover:bg-amber-100 disabled:opacity-50 dark:border-amber-400/30 dark:bg-transparent dark:text-amber-50 dark:hover:bg-amber-500/20"
                >
                    <span wire:loading.remove wire:target="resend">{{ __('onboarding.verify_resend') }}</span>
                    <span wire:loading wire:target="resend">{{ __('onboarding.verify_resending') }}</span>
                </button>
                <button
                    type="button"
                    wire:click="toggleForm"
                    class="rounded-md border border-amber-400/60 bg-white px-3 py-2 text-sm font-medium text-amber-950 hover:bg-amber-100 dark:border-amber-400/30 dark:bg-transparent dark:text-amber-50 dark:hover:bg-amber-500/20"
                >
                    {{ __('onboarding.verify_change_email') }}
                </button>
            </div>
        </div>

        @if ($showForm)
            <form wire:submit="updateEmail" class="mt-4 flex flex-col gap-3 border-t border-amber-300/40 pt-4 sm:flex-row sm:items-end">
                <div class="min-w-0 flex-1">
                    <label class="mb-1 block text-xs font-medium">{{ __('onboarding.verify_change_email_label') }}</label>
                    <input
                        type="email"
                        wire:model="email"
                        class="block w-full rounded-md border border-amber-300/60 bg-white px-3 py-2 text-sm text-amber-950 dark:border-amber-400/30 dark:bg-background dark:text-foreground"
                        required
                    >
                    @error('email') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-amber-800/70 dark:text-amber-100/70">{{ __('onboarding.verify_change_email_hint') }}</p>
                </div>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="rounded-md bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 disabled:opacity-50 dark:bg-amber-500 dark:hover:bg-amber-400"
                >
                    {{ __('onboarding.verify_change_email_submit') }}
                </button>
            </form>
        @endif
    </div>
@endif

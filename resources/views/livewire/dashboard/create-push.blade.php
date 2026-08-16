@php
    use App\PushNotificationRecipientType;

    $controlClass = 'block w-full rounded-md border border-border bg-background px-3 py-2 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashError)
        <div class="rounded-lg border border-red-300/50 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100">{{ $flashError }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold">{{ __('dashboard.pushes_create') }}</h2>
        <x-dashboard.button variant="ghost" href="{{ route('dashboard.pushes') }}">{{ __('dashboard.back') }}</x-dashboard.button>
    </div>

    <x-dashboard.card>
        <p class="mb-6 text-sm text-muted-foreground">
            {{ __('app.push_notifications_subscriber_count', ['count' => $subscriberCount]) }}
        </p>

        <form wire:submit="send" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.pushes_title_field') }}</label>
                <input type="text" wire:model="title" maxlength="50" class="{{ $controlClass }} h-10">
                @error('title') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.pushes_body_field') }}</label>
                <textarea wire:model="body" maxlength="120" rows="3" class="{{ $controlClass }}"></textarea>
                @error('body') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.pushes_scheduled') }}</label>
                <input type="datetime-local" wire:model="scheduled_at" class="{{ $controlClass }} h-10">
                <p class="mt-1 text-xs text-muted-foreground">{{ __('app.push_notifications_field_scheduled_at_helper') }}</p>
                @error('scheduled_at') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('dashboard.pushes_recipients') }}</label>
                <div class="space-y-2">
                    @foreach (PushNotificationRecipientType::cases() as $type)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" wire:model.live="recipient_type" value="{{ $type->value }}" class="border-border">
                            {{ $type->label() }}
                        </label>
                    @endforeach
                </div>
                @error('recipient_type') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            @if ($recipient_type === PushNotificationRecipientType::Selected->value)
                <div>
                    <label class="mb-2 block text-sm font-medium">{{ __('app.push_notifications_field_select_guests') }}</label>
                    <div class="max-h-64 space-y-2 overflow-y-auto rounded-md border border-border p-3">
                        @forelse ($subscriberOptions as $id => $label)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="selected_guest_ids" value="{{ $id }}" class="rounded border-border">
                                {{ $label }}
                            </label>
                        @empty
                            <p class="text-sm text-muted-foreground">{{ __('dashboard.empty') }}</p>
                        @endforelse
                    </div>
                    @error('selected_guest_ids') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit">{{ __('dashboard.pushes_send') }}</x-dashboard.button>
                <x-dashboard.button variant="secondary" href="{{ route('dashboard.pushes') }}">{{ __('dashboard.cancel') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</div>

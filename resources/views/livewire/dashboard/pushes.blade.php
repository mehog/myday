<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold">{{ __('dashboard.pushes_title') }}</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ __('app.push_notifications_subscriber_count', ['count' => $subscriberCount]) }}
            </p>
        </div>
        @if ($wedding)
            <x-dashboard.button href="{{ route('dashboard.pushes.create') }}">
                {{ __('dashboard.pushes_create') }}
            </x-dashboard.button>
        @endif
    </div>

    @if (! $wedding)
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.no_wedding') }}</p>
        </x-dashboard.card>
    @elseif ($logs->isEmpty())
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.empty') }}</p>
        </x-dashboard.card>
    @else
        <x-dashboard.card>
            <div class="-mx-4 overflow-x-auto md:-mx-5">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-border text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 md:px-5">{{ __('dashboard.pushes_title_field') }}</th>
                            <th class="px-4 py-3">{{ __('dashboard.pushes_body_field') }}</th>
                            <th class="px-4 py-3">{{ __('app.push_notifications_status') }}</th>
                            <th class="px-4 py-3">{{ __('dashboard.pushes_recipients') }}</th>
                            <th class="px-4 py-3">{{ __('app.push_notifications_sent_to') }}</th>
                            <th class="px-4 py-3">{{ __('dashboard.pushes_scheduled') }}</th>
                            <th class="px-4 py-3 md:px-5">{{ __('dashboard.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr class="border-t border-border" wire:key="push-{{ $log->id }}">
                                <td class="px-4 py-3 md:px-5 font-medium">{{ $log->title }}</td>
                                <td class="px-4 py-3 max-w-xs truncate">{{ $log->body }}</td>
                                <td class="px-4 py-3">{{ $log->status?->label() }}</td>
                                <td class="px-4 py-3">{{ $log->recipient_type?->label() }}</td>
                                <td class="px-4 py-3">{{ $log->sent_to_count }}</td>
                                <td class="px-4 py-3">{{ $log->scheduled_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 md:px-5">
                                    <x-dashboard.button
                                        type="button"
                                        variant="destructive"
                                        class="!px-2 !py-1 text-xs"
                                        wire:click="deleteLog({{ $log->id }})"
                                        wire:confirm="{{ __('dashboard.delete') }}?"
                                    >
                                        {{ __('dashboard.delete') }}
                                    </x-dashboard.button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-dashboard.card>
    @endif
</div>

@php
    $controlClass = 'block h-10 w-full rounded-md border border-border bg-background px-3 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="hidden text-xl font-semibold lg:block">{{ __('checklist.title') }}</h2>
        @if (! $locked)
            <div class="hidden lg:ml-auto lg:flex">
                <x-dashboard.button type="button" wire:click="openCreate">
                    <x-dashboard.icon name="plus" class="h-4 w-4" />
                    {{ __('checklist.add') }}
                </x-dashboard.button>
            </div>
        @endif
    </div>

    @if ($locked)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            {{ __('app.wedding_archived_readonly') }}
        </div>
    @endif

    <x-dashboard.card>
        <div class="flex flex-col gap-4 sm:flex-row items-center justify-center text-center">
            <div
                class="checklist-ring"
                style="--progress: {{ $summary['percent'] }}"
                aria-hidden="true"
            >
                <span class="checklist-ring-inner">{{ $summary['percent'] }}%</span>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ __('checklist.summary_label') }}</p>
                <p class="mt-1 text-xl font-semibold tabular-nums">
                    {{ __('checklist.summary_value', ['completed' => $summary['completed'], 'total' => $summary['total']]) }}
                </p>
                <p class="mt-1 text-sm text-muted-foreground">{{ __('checklist.summary_percent', ['percent' => $summary['percent']]) }}</p>
            </div>
        </div>
    </x-dashboard.card>

    <div class="flex w-full min-w-0 justify-center border-b border-border pb-3">
        <x-dashboard.pills
            name="tab"
            :selected="$tab"
            :options="[
                'all' => ['label' => __('checklist.tabs.all')],
                'mine' => ['label' => __('checklist.tabs.mine')],
                'predefined' => ['label' => __('checklist.tabs.predefined')],
                'completed' => ['label' => __('checklist.tabs.completed')],
            ]"
            :label="__('checklist.tabs_label')"
        />
    </div>

    @if ($isEmpty)
        <x-dashboard.card>
            <p class="font-medium">{{ $tab === 'all' ? __('checklist.empty_heading') : __('checklist.empty_filtered') }}</p>
            @if ($tab === 'all')
                <p class="mt-1 text-sm text-muted-foreground">{{ __('checklist.empty_description') }}</p>
            @endif
        </x-dashboard.card>
    @else
        <div class="space-y-5">
            @foreach ($grouped as $group)
                @php
                    $period = $group['period'];
                    $rows = $group['rows'];
                    $periodSummary = $group['summary'];
                @endphp
                <div
                    wire:key="period-{{ $period }}"
                    x-data="{
                        collapsed: (() => {
                            try {
                                return JSON.parse(localStorage.getItem('checklist_periods') || '{}')['{{ $period }}'] ?? false;
                            } catch (e) {
                                return false;
                            }
                        })(),
                        toggle() {
                            this.collapsed = !this.collapsed;
                            let stored = {};
                            try {
                                stored = JSON.parse(localStorage.getItem('checklist_periods') || '{}') || {};
                            } catch (e) {
                                stored = {};
                            }
                            stored['{{ $period }}'] = this.collapsed;
                            localStorage.setItem('checklist_periods', JSON.stringify(stored));
                        }
                    }"
                >
                    <x-dashboard.card flush>
                        <x-slot:header>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-3 text-left"
                                x-on:click="toggle()"
                                x-bind:aria-expanded="(!collapsed).toString()"
                                aria-label="{{ \App\WeddingTaskPeriod::from($period)->label() }}"
                            >
                                <div class="flex min-w-0 flex-wrap items-center gap-2">
                                    <h3 class="font-medium">{{ \App\WeddingTaskPeriod::from($period)->label() }}</h3>
                                    <span class="text-sm text-muted-foreground tabular-nums">
                                        {{ __('checklist.period_progress', ['completed' => $periodSummary['completed'], 'total' => $periodSummary['total']]) }}
                                    </span>
                                </div>
                                <x-dashboard.icon
                                    name="chevron-down"
                                    class="h-4 w-4 shrink-0 text-muted-foreground transition-transform"
                                    x-bind:class="{ 'rotate-180': !collapsed }"
                                />
                            </button>
                        </x-slot:header>
                        <div x-show="!collapsed" x-cloak class="p-4 md:p-5">
                            @foreach ($rows as $row)
                                @php
                                    /** @var \App\Models\WeddingTask $task */
                                    $task = $row['task'];
                                @endphp
                                <div
                                    class="flex gap-3 border-b border-border py-3 last:border-0"
                                    wire:key="task-{{ $task->id }}"
                                >
                                    <button
                                        type="button"
                                        class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded border {{ $task->isCompleted() ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-background' }} {{ $locked ? 'cursor-not-allowed opacity-60' : '' }}"
                                        wire:click="toggle({{ $task->id }})"
                                        @disabled($locked)
                                        aria-pressed="{{ $task->isCompleted() ? 'true' : 'false' }}"
                                        aria-label="{{ $row['title'] }}"
                                    >
                                        @if ($task->isCompleted())
                                            <x-dashboard.icon name="check" class="h-3.5 w-3.5" />
                                        @endif
                                    </button>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button
                                                type="button"
                                                @class([
                                                    'text-left font-medium',
                                                    'text-muted-foreground line-through' => $task->isCompleted(),
                                                    'cursor-pointer hover:text-foreground' => ! $locked,
                                                    'cursor-not-allowed opacity-60' => $locked,
                                                ])
                                                wire:click="toggle({{ $task->id }})"
                                                @disabled($locked)
                                            >
                                                {{ $row['title'] }}
                                            </button>
                                            @if ($task->priority === \App\WeddingTaskPriority::High && ! $task->isCompleted())
                                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-800 dark:bg-red-500/15 dark:text-red-200">{{ __('checklist.priority_high_badge') }}</span>
                                            @endif
                                        </div>
                                        @if ($row['description'] && ! $task->isSystem())
                                            <p class="mt-1 text-sm text-muted-foreground">{{ $row['description'] }}</p>
                                        @endif
                                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-muted-foreground">
                                            @if ($row['due_label'])
                                                <span @class(['tabular-nums', 'text-red-600 dark:text-red-400' => $task->isOverdue()])>{{ $row['due_label'] }}</span>
                                            @endif
                                            @if ($row['progress'])
                                                <span class="tabular-nums">{{ $row['progress']['label'] }}</span>
                                            @endif
                                            @if ($row['action_url'])
                                                <a href="{{ $row['action_url'] }}" class="font-medium text-primary hover:underline">{{ __('checklist.open_named', ['name' => $row['action_label']]) }}</a>
                                            @endif
                                        </div>
                                    </div>
                                    @if (! $locked && ! $task->isSystem())
                                        <div class="flex shrink-0 flex-wrap gap-2 self-start">
                                            <x-dashboard.button type="button" variant="secondary" class="!px-2 !py-1 text-xs" wire:click="openEdit({{ $task->id }})">{{ __('dashboard.edit') }}</x-dashboard.button>
                                            <x-dashboard.button type="button" variant="destructive" class="!px-2 !py-1 text-xs" wire:click="delete({{ $task->id }})" wire:confirm="{{ __('checklist.delete_confirm') }}">{{ __('dashboard.delete') }}</x-dashboard.button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-dashboard.card>
                </div>
            @endforeach
        </div>
    @endif

    <x-dashboard.fab wire:click="openCreate" :label="__('checklist.add')" :show="! $locked" />

    <x-dashboard.modal :show="$showModal" :title="$editingId ? __('dashboard.edit') : __('checklist.add')">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('checklist.field_title') }}</label>
                <input type="text" wire:model="title" class="{{ $controlClass }}">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('checklist.field_due_date') }}</label>
                <input type="date" wire:model="due_date" class="{{ $controlClass }}">
                @error('due_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('checklist.field_notes') }}</label>
                <textarea wire:model="notes" rows="3" class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.modal>
</div>

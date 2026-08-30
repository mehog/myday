@vite(['resources/js/seating-plan.js'])

@php
    $emptySeatLabel = __('seating.empty_seat');
    $seatLabelPrefix = __('seating.seat');
@endphp

<div
    class="space-y-4"
    x-bind:class="viewMode === 'floor' ? 'flex h-full min-h-0 flex-col overflow-hidden' : ''"
    x-data="{
        viewMode: window.matchMedia('(min-width: 1024px)').matches ? 'floor' : 'list',
        selectedTable: null,
        inspectorOpen: false,
        inspectorCollapsed: false,
        zoomLabel: '100%',
        seatCount: { assigned: 0, total: 0 },
        planTables: @js(($seatingPlan['tables'] ?? [])),
        allGuests: @js($this->getGuests()),
        expandedTables: {},
        unassignedSearch: '',
        addTableOpen: false,
        floorFullscreen: false,
        tableTypeMenuOpen: false,
        viewOptionsMenuOpen: false,
        assignGuestTarget: null,
        emptySeatLabel: @js($emptySeatLabel),
        seatLabelPrefix: @js($seatLabelPrefix),
        chairModal: {
            open: false,
            tableId: null,
            seatIndex: null,
            currentGuestId: null,
            search: '',
        },
        inspectorLabel: '',
        inspectorChairCount: 0,
        inspectorRotation: 0,
        init() {
            this.seatCount.total = this.allGuests.length;
            this.lockFloorPageScroll();
            this.$nextTick(() => this.initEditor());
        },
        destroy() {
            this.lockFloorPageScroll(false);
        },
        lockFloorPageScroll(force) {
            const main = document.querySelector('.dashboard-main');
            if (! main) {
                return;
            }

            const lock = force === false ? false : this.viewMode === 'floor';
            main.classList.toggle('dashboard-main-locked', lock);
            main.style.overflow = lock ? 'hidden' : '';
            main.style.overscrollBehavior = lock ? 'none' : '';
            if (lock) {
                main.scrollTop = 0;
            }
        },
        initEditor() {
            if (! this.$refs.canvasContainer) {
                return;
            }

            if (window.seatingPlanEditor) {
                this.syncFromEditor();
                this.$nextTick(() => window.seatingPlanEditor?.resize());
                return;
            }

            window.seatingPlanEditor = window.createSeatingPlanEditor({
                container: this.$refs.canvasContainer,
                wire: $wire,
                initialPlan: @js($seatingPlan),
                guests: this.allGuests,
                labels: @js($this->getEditorLabels()),
                exportPdfUrl: @js(route('seating-plan.export-pdf')),
                onSelectionChange: (table) => {
                    this.selectedTable = table;
                    this.inspectorOpen = table !== null;
                },
                onChairClick: (data) => this.openChairModal(data),
            });

            this.syncFromEditor();
        },
        syncFromEditor() {
            const plan = window.seatingPlanEditor?.getPlan();
            if (plan?.tables) {
                this.planTables = plan.tables;
            }
            this.seatCount.assigned = window.seatingPlanEditor?.getAssignedIds()?.length ?? 0;
        },
        switchViewMode(mode) {
            this.viewMode = mode;
            if (mode !== 'floor' && this.floorFullscreen) {
                this.floorFullscreen = false;
            }
            this.lockFloorPageScroll();
            if (mode === 'floor') {
                this.$nextTick(() => window.seatingPlanEditor?.resize());
            }
        },
        toggleFloorFullscreen() {
            this.floorFullscreen = ! this.floorFullscreen;
            this.$nextTick(() => window.seatingPlanEditor?.resize());
        },
        openChairModal(data) {
            this.assignGuestTarget = null;
            this.chairModal = {
                open: true,
                search: '',
                tableId: data.tableId,
                seatIndex: data.seatIndex,
                currentGuestId: data.guestId ?? null,
            };
        },
        openAssignForGuest(guestId) {
            this.assignGuestTarget = guestId;
            this.chairModal = {
                open: true,
                search: '',
                tableId: null,
                seatIndex: null,
                currentGuestId: null,
            };
        },
        closeChairModal() {
            this.chairModal.open = false;
            this.assignGuestTarget = null;
        },
        assignGuest(guestId) {
            window.seatingPlanEditor?.assignToSeat(
                this.chairModal.tableId,
                this.chairModal.seatIndex,
                guestId,
            );
            this.closeChairModal();
        },
        assignGuestToSeat(tableId, seatIndex) {
            if (! this.assignGuestTarget) {
                return;
            }
            window.seatingPlanEditor?.assignToSeat(tableId, seatIndex, this.assignGuestTarget);
            this.closeChairModal();
        },
        clearSeat() {
            window.seatingPlanEditor?.clearSeat(
                this.chairModal.tableId,
                this.chairModal.seatIndex,
            );
            this.closeChairModal();
        },
        guestName(guestId) {
            if (guestId === null || guestId === undefined) {
                return '';
            }
            return this.allGuests.find((guest) => guest.id === guestId)?.name
                ?? window.seatingPlanEditor?.getGuestName?.(guestId)
                ?? '';
        },
        occupiedCount(table) {
            return (table.seats ?? []).filter(Boolean).length;
        },
        get filteredGuests() {
            const query = this.chairModal.search.toLowerCase();
            const assignedIds = window.seatingPlanEditor?.getAssignedIds() ?? [];

            return this.allGuests.filter((guest) =>
                ! assignedIds.includes(guest.id) &&
                guest.name.toLowerCase().includes(query),
            );
        },
        get unassignedGuests() {
            const query = this.unassignedSearch.toLowerCase();
            const assignedIds = window.seatingPlanEditor?.getAssignedIds() ?? [];

            return this.allGuests.filter((guest) =>
                ! assignedIds.includes(guest.id) &&
                guest.name.toLowerCase().includes(query),
            );
        },
        get emptySeatsByTable() {
            return this.planTables
                .map((table) => ({
                    table,
                    seats: (table.seats ?? [])
                        .map((guestId, index) => ({ guestId, index }))
                        .filter((seat) => seat.guestId === null || seat.guestId === undefined),
                }))
                .filter((entry) => entry.seats.length > 0);
        },
        get currentGuestName() {
            if (! this.chairModal.currentGuestId) {
                return '';
            }

            return this.guestName(this.chairModal.currentGuestId);
        },
        syncInspector() {
            if (! this.selectedTable) {
                return;
            }
            this.inspectorLabel = this.selectedTable.label ?? '';
            this.inspectorChairCount = this.selectedTable.chair_count ?? 0;
            this.inspectorRotation = this.selectedTable.rotation ?? 0;
        },
        updateLabel() {
            window.seatingPlanEditor?.updateSelectedLabel(this.inspectorLabel);
        },
        updateTableLabel(tableId, label) {
            window.seatingPlanEditor?.updateTableLabel(tableId, label);
        },
        addChair() {
            window.seatingPlanEditor?.addChairToSelected();
            this.syncInspector();
        },
        removeChair() {
            window.seatingPlanEditor?.removeChairFromSelected();
            this.syncInspector();
        },
        rotateTable(deg) {
            window.seatingPlanEditor?.rotateSelected(deg);
            this.syncInspector();
        },
        deleteTable() {
            window.seatingPlanEditor?.deleteSelectedTable();
            this.inspectorOpen = false;
            this.selectedTable = null;
        },
        closeInspector() {
            this.selectedTable = null;
            this.inspectorOpen = false;
        },
        addTable(type) {
            window.seatingPlanEditor?.addTable(type);
            this.addTableOpen = false;
            if (this.viewMode === 'list') {
                const tables = window.seatingPlanEditor?.getPlan()?.tables ?? [];
                const last = tables[tables.length - 1];
                if (last) {
                    this.expandedTables[last.id] = true;
                }
            }
        },
        toggleTable(tableId) {
            this.expandedTables[tableId] = ! this.expandedTables[tableId];
        },
    }"
    x-effect="syncInspector(); lockFloorPageScroll()"
    x-on:seating-zoom-changed.window="zoomLabel = $event.detail.label"
    x-on:seating-seats-changed.window="seatCount.assigned = $event.detail.assigned"
    x-on:seating-plan-changed.window="planTables = $event.detail.plan.tables; seatCount.assigned = window.seatingPlanEditor?.getAssignedIds()?.length ?? 0"
    x-on:keydown.escape.window="if (floorFullscreen && !chairModal.open && !addTableOpen) { floorFullscreen = false; $nextTick(() => window.seatingPlanEditor?.resize()) }"
>
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif

    <div class="sticky top-0 z-20 -mx-4 shrink-0 space-y-3 border-b border-border bg-background/95 px-4 py-3 backdrop-blur md:-mx-6 md:px-6 lg:static lg:z-auto lg:mx-0 lg:border-0 lg:bg-transparent lg:p-0 lg:backdrop-blur-none">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <h2 class="hidden text-xl font-semibold lg:block">{{ __('seating.page_title') }}</h2>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    <span x-text="seatCount.assigned"></span>
                    /
                    <span x-text="seatCount.total"></span>
                    {{ __('seating.seats_label') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-dashboard.button type="button" x-on:click="window.seatingPlanEditor?.save(true)">
                    {{ __('seating.save') }}
                </x-dashboard.button>
                @if ($this->canExportSeatingPdf())
                    <x-dashboard.button type="button" variant="secondary" id="seating-export-pdf-btn" x-on:click="window.seatingPlanEditor?.exportPdf()">
                        {{ __('seating.export_pdf') }}
                    </x-dashboard.button>
                @else
                    <x-dashboard.button type="button" variant="secondary" id="seating-export-pdf-btn" x-on:click="Livewire.dispatch('open-upgrade-modal')">
                        {{ __('seating.export_pdf') }}
                    </x-dashboard.button>
                @endif
            </div>
        </div>

        <div class="grid max-w-md grid-cols-2 gap-1 rounded-xl bg-muted p-1" role="tablist">
            <button
                type="button"
                role="tab"
                class="rounded-lg px-3 py-2 text-sm font-medium transition"
                :class="viewMode === 'list' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground'"
                x-on:click="switchViewMode('list')"
            >
                {{ __('seating.mode_list') }}
            </button>
            <button
                type="button"
                role="tab"
                class="rounded-lg px-3 py-2 text-sm font-medium transition"
                :class="viewMode === 'floor' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground'"
                x-on:click="switchViewMode('floor')"
            >
                {{ __('seating.mode_floor') }}
            </button>
        </div>
    </div>

    {{-- List mode --}}
    <div class="space-y-4" x-show="viewMode === 'list'" x-cloak>
        <x-dashboard.card>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-medium">{{ __('seating.unassigned') }}</h3>
                    <p class="text-xs text-muted-foreground">
                        <span x-text="unassignedGuests.length"></span>
                        {{ __('seating.unassigned_count_suffix') }}
                    </p>
                </div>
                <x-dashboard.button type="button" variant="secondary" class="!px-2 !py-1 text-xs" x-on:click="addTableOpen = true">
                    {{ __('seating.add_table') }}
                </x-dashboard.button>
            </div>

            <div class="mt-3">
                <input
                    type="search"
                    x-model="unassignedSearch"
                    class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm"
                    placeholder="{{ __('seating.search_guest') }}"
                />
            </div>

            <ul class="mt-3 max-h-56 divide-y divide-border overflow-y-auto rounded-lg border border-border lg:max-h-72">
                <template x-for="guest in unassignedGuests" :key="'u-' + guest.id">
                    <li>
                        <button
                            type="button"
                            class="flex w-full items-start justify-between gap-2 px-3 py-2.5 text-left text-sm hover:bg-accent"
                            x-on:click="openAssignForGuest(guest.id)"
                        >
                            <span class="min-w-0">
                                <span
                                    class="block font-medium"
                                    :class="{
                                        'text-rose-600 dark:text-rose-400': guest.is_couple,
                                        'text-muted-foreground': guest.is_plus_one || guest.is_child,
                                    }"
                                    x-text="guest.name"
                                ></span>
                                <template x-if="guest.labels?.length">
                                    <span class="mt-0.5 block text-xs text-muted-foreground" x-text="guest.labels.join(' · ')"></span>
                                </template>
                            </span>
                            <span class="shrink-0 text-xs font-medium text-primary">{{ __('seating.assign_to_table') }}</span>
                        </button>
                    </li>
                </template>
                <template x-if="unassignedGuests.length === 0">
                    <li class="px-3 py-4 text-center text-sm text-muted-foreground">
                        {{ __('seating.no_unassigned') }}
                    </li>
                </template>
            </ul>
        </x-dashboard.card>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-medium">{{ __('seating.tables_heading') }}</h3>
                <x-dashboard.button type="button" class="!px-2 !py-1 text-xs" x-on:click="addTableOpen = true">
                    {{ __('seating.add_table') }}
                </x-dashboard.button>
            </div>

            <template x-if="planTables.length === 0">
                <x-dashboard.card>
                    <p class="text-sm text-muted-foreground">{{ __('seating.no_tables_yet') }}</p>
                    <div class="mt-3">
                        <x-dashboard.button type="button" x-on:click="addTableOpen = true">
                            {{ __('seating.add_table') }}
                        </x-dashboard.button>
                    </div>
                </x-dashboard.card>
            </template>

            <div class="grid gap-3 lg:grid-cols-2">
                <template x-for="table in planTables" :key="table.id">
                    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left"
                            x-on:click="toggleTable(table.id)"
                        >
                            <div class="min-w-0">
                                <p class="truncate font-medium" x-text="table.label"></p>
                                <p class="text-xs text-muted-foreground">
                                    <span x-text="occupiedCount(table)"></span>
                                    /
                                    <span x-text="table.chair_count"></span>
                                    {{ __('seating.seats_label') }}
                                </p>
                            </div>
                            <x-dashboard.icon
                                name="chevron-down"
                                class="h-4 w-4 shrink-0 text-muted-foreground transition"
                                x-bind:class="expandedTables[table.id] ? 'rotate-180' : ''"
                            />
                        </button>

                        <div
                            x-show="expandedTables[table.id]"
                            x-cloak
                            class="space-y-3 border-t border-border px-4 py-3"
                        >
                            <div>
                                <label class="mb-1 block text-xs font-medium text-muted-foreground">{{ __('seating.table_label') }}</label>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm"
                                    :value="table.label"
                                    x-on:change="updateTableLabel(table.id, $event.target.value)"
                                />
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-medium text-muted-foreground">{{ __('seating.chairs') }}</span>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border"
                                        x-on:click="window.seatingPlanEditor?.removeChairFromTable(table.id)"
                                        aria-label="{{ __('seating.remove_chair') }}"
                                    >
                                        <x-dashboard.icon name="minus" class="h-4 w-4" />
                                    </button>
                                    <span class="min-w-[2rem] text-center text-sm font-semibold" x-text="table.chair_count"></span>
                                    <button
                                        type="button"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border"
                                        x-on:click="window.seatingPlanEditor?.addChairToTable(table.id)"
                                        aria-label="{{ __('seating.add_chair') }}"
                                    >
                                        <x-dashboard.icon name="plus" class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <ul class="divide-y divide-border overflow-hidden rounded-lg border border-border">
                                <template x-for="(guestId, seatIndex) in table.seats" :key="table.id + '-seat-' + seatIndex">
                                    <li>
                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between gap-2 px-3 py-2.5 text-left text-sm hover:bg-accent"
                                            x-on:click="openChairModal({ tableId: table.id, seatIndex, guestId })"
                                        >
                                            <span class="text-xs text-muted-foreground" x-text="seatLabelPrefix + ' ' + (seatIndex + 1)"></span>
                                            <span
                                                class="min-w-0 flex-1 truncate font-medium"
                                                :class="guestId ? 'text-foreground' : 'text-muted-foreground'"
                                                x-text="guestId ? guestName(guestId) : emptySeatLabel"
                                            ></span>
                                        </button>
                                    </li>
                                </template>
                            </ul>

                            <x-dashboard.button
                                type="button"
                                variant="destructive"
                                class="w-full"
                                x-on:click="window.seatingPlanEditor?.deleteTable(table.id)"
                            >
                                {{ __('seating.delete_table') }}
                            </x-dashboard.button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Floor editor stays mounted (off-screen when list) so Konva keeps a real size --}}
    <div
        class="flex flex-col overflow-hidden"
        x-bind:class="viewMode === 'floor'
            ? (floorFullscreen
                ? 'fixed inset-0 z-50 h-dvh bg-background opacity-100'
                : 'relative -mx-1 min-h-0 flex-1 overscroll-none opacity-100 sm:-mx-6 lg:-mx-8')
            : 'pointer-events-none fixed left-[-9999px] top-0 h-[480px] w-[360px] opacity-0'"
    >
        <div class="flex shrink-0 items-center gap-2 border-b border-border bg-card px-3 py-2 sm:px-4">
            <div class="relative">
                <button
                    type="button"
                    class="inline-flex h-9 items-center gap-1.5 rounded-md border border-border bg-background px-2.5 text-sm font-medium hover:bg-accent"
                    x-on:click="tableTypeMenuOpen = !tableTypeMenuOpen; viewOptionsMenuOpen = false"
                    x-bind:aria-expanded="tableTypeMenuOpen"
                >
                    {{ __('seating.select_table_type') }}
                    <x-dashboard.icon name="chevron-down" class="h-3.5 w-3.5 text-muted-foreground" />
                </button>
                <div
                    x-show="tableTypeMenuOpen"
                    x-on:click.outside="tableTypeMenuOpen = false"
                    x-cloak
                    class="absolute left-0 z-30 mt-1 w-48 rounded-md border border-border bg-popover p-1 shadow-lg"
                >
                    <button type="button" class="flex w-full items-center rounded-sm px-2.5 py-2 text-left text-sm hover:bg-accent" x-on:click="window.seatingPlanEditor?.addTable('round'); tableTypeMenuOpen = false">
                        {{ __('seating.add_round') }}
                    </button>
                    <button type="button" class="flex w-full items-center rounded-sm px-2.5 py-2 text-left text-sm hover:bg-accent" x-on:click="window.seatingPlanEditor?.addTable('rect'); tableTypeMenuOpen = false">
                        {{ __('seating.add_rect') }}
                    </button>
                    <button type="button" class="flex w-full items-center rounded-sm px-2.5 py-2 text-left text-sm hover:bg-accent" x-on:click="window.seatingPlanEditor?.addTable('head'); tableTypeMenuOpen = false">
                        {{ __('seating.add_head') }}
                    </button>
                </div>
            </div>

            <div class="ml-auto flex items-center gap-1">
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-background text-muted-foreground hover:bg-accent"
                    x-on:click="window.seatingPlanEditor?.zoomOut()"
                    aria-label="{{ __('seating.zoom_out') }}"
                >
                    <x-dashboard.icon name="minus" class="h-4 w-4" />
                </button>
                <span class="min-w-[3rem] text-center text-sm text-muted-foreground" x-text="zoomLabel"></span>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-background text-muted-foreground hover:bg-accent"
                    x-on:click="window.seatingPlanEditor?.zoomIn()"
                    aria-label="{{ __('seating.zoom_in') }}"
                >
                    <x-dashboard.icon name="plus" class="h-4 w-4" />
                </button>

                <div class="relative">
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-background text-muted-foreground hover:bg-accent"
                        x-on:click="viewOptionsMenuOpen = !viewOptionsMenuOpen; tableTypeMenuOpen = false"
                        x-bind:aria-expanded="viewOptionsMenuOpen"
                        aria-label="{{ __('seating.view_options') }}"
                    >
                        <x-dashboard.icon name="more" class="h-4 w-4" />
                    </button>
                    <div
                        x-show="viewOptionsMenuOpen"
                        x-on:click.outside="viewOptionsMenuOpen = false"
                        x-cloak
                        class="absolute right-0 z-30 mt-1 w-48 rounded-md border border-border bg-popover p-1 shadow-lg"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 rounded-sm px-2.5 py-2 text-left text-sm hover:bg-accent"
                            x-on:click="window.seatingPlanEditor?.resetZoom(); viewOptionsMenuOpen = false"
                        >
                            {{ __('seating.reset_zoom') }}
                        </button>
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 rounded-sm px-2.5 py-2 text-left text-sm hover:bg-accent"
                            x-on:click="toggleFloorFullscreen(); viewOptionsMenuOpen = false"
                        >
                            <span class="inline-flex h-3.5 w-3.5 shrink-0" x-show="!floorFullscreen">
                                <x-dashboard.icon name="maximize" class="h-3.5 w-3.5" />
                            </span>
                            <span class="inline-flex h-3.5 w-3.5 shrink-0" x-show="floorFullscreen" x-cloak>
                                <x-dashboard.icon name="minimize" class="h-3.5 w-3.5" />
                            </span>
                            <span x-text="floorFullscreen ? @js(__('seating.exit_fullscreen')) : @js(__('seating.fullscreen'))"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <p class="shrink-0 border-b border-border bg-muted/40 px-3 py-1.5 text-xs text-muted-foreground lg:hidden" x-show="!floorFullscreen">
            {{ __('seating.floor_hint') }}
        </p>

        <div class="relative flex min-h-0 flex-1">
            <div class="relative min-w-0 flex-1 touch-none bg-[#f8f9fb] dark:bg-gray-950">
                <div
                    x-ref="canvasContainer"
                    class="h-full w-full"
                    wire:ignore
                ></div>

                <div class="absolute right-3 top-3 z-10 hidden w-64 lg:block">
                    <div
                        class="flex items-center justify-between border border-border bg-card px-3 py-2 shadow-lg"
                        :class="inspectorCollapsed ? 'rounded-xl' : 'rounded-t-xl'"
                    >
                        <span class="text-sm font-semibold">{{ __('seating.inspector_heading') }}</span>
                        <button
                            type="button"
                            class="rounded-md p-1 text-muted-foreground hover:bg-accent"
                            x-on:click="inspectorCollapsed = !inspectorCollapsed"
                        >
                            <x-dashboard.icon name="chevron-down" class="h-4 w-4" x-bind:class="inspectorCollapsed ? '' : 'rotate-180'" />
                        </button>
                    </div>

                    <div
                        x-show="!inspectorCollapsed"
                        x-transition
                        class="max-h-[calc(100vh-12rem)] overflow-y-auto rounded-b-xl border-x border-b border-border bg-card shadow-lg"
                    >
                        <template x-if="inspectorOpen">
                            <div class="flex flex-col gap-4 p-4">
                                @include('livewire.dashboard.partials.seating-inspector-fields')
                            </div>
                        </template>
                        <template x-if="!inspectorOpen">
                            <div class="flex flex-col gap-4 p-4">
                                <p class="text-center text-sm text-muted-foreground">{{ __('seating.select_table') }}</p>
                                <div class="flex items-center justify-between border-t border-border pt-4">
                                    <span class="text-xs font-medium text-muted-foreground">{{ __('seating.seats_label') }}</span>
                                    <span class="text-sm font-semibold" x-text="seatCount.assigned + ' / ' + seatCount.total"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="lg:hidden"
            x-show="inspectorOpen && viewMode === 'floor'"
            x-cloak
        >
            <div class="border-t border-border bg-card px-4 pb-[max(1rem,env(safe-area-inset-bottom))] pt-3 shadow-[0_-8px_24px_rgba(0,0,0,0.08)]">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm font-semibold">{{ __('seating.inspector_heading') }}</span>
                    <button type="button" class="text-sm text-muted-foreground" x-on:click="closeInspector()">
                        {{ __('seating.close_inspector') }}
                    </button>
                </div>
                <div class="max-h-56 space-y-4 overflow-y-auto">
                    @include('livewire.dashboard.partials.seating-inspector-fields')
                </div>
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div
            x-show="addTableOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-on:keydown.escape.window="if (addTableOpen) addTableOpen = false"
        >
            <div class="absolute inset-0 bg-black/50" x-on:click="addTableOpen = false"></div>
            <div class="relative z-10 w-full max-w-sm rounded-2xl border border-border bg-card p-4 shadow-xl">
                <h3 class="mb-3 text-base font-semibold">{{ __('seating.add_table') }}</h3>
                <div class="grid gap-2">
                    <x-dashboard.button type="button" variant="secondary" class="w-full justify-center" x-on:click="addTable('round')">
                        {{ __('seating.add_round') }}
                    </x-dashboard.button>
                    <x-dashboard.button type="button" variant="secondary" class="w-full justify-center" x-on:click="addTable('rect')">
                        {{ __('seating.add_rect') }}
                    </x-dashboard.button>
                    <x-dashboard.button type="button" variant="secondary" class="w-full justify-center" x-on:click="addTable('head')">
                        {{ __('seating.add_head') }}
                    </x-dashboard.button>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div
            x-show="chairModal.open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-on:keydown.escape.window="if (chairModal.open) closeChairModal()"
        >
            <div class="absolute inset-0 bg-black/50" x-on:click="closeChairModal()"></div>

            <div class="relative z-10 flex max-h-[85dvh] w-full max-w-sm flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-xl">
                <div class="flex items-center justify-between border-b border-border px-4 py-3">
                    <span class="text-sm font-semibold" x-text="assignGuestTarget ? @js(__('seating.choose_seat')) : @js(__('seating.assign_guest'))"></span>
                    <button type="button" class="text-muted-foreground hover:text-foreground" x-on:click="closeChairModal()">&times;</button>
                </div>

                <template x-if="assignGuestTarget">
                    <div class="min-h-0 flex-1 overflow-y-auto">
                        <div class="border-b border-border bg-muted/40 px-4 py-2 text-sm" x-text="guestName(assignGuestTarget)"></div>
                        <template x-if="emptySeatsByTable.length === 0">
                            <p class="px-4 py-6 text-center text-sm text-muted-foreground">{{ __('seating.no_empty_seats') }}</p>
                        </template>
                        <template x-for="entry in emptySeatsByTable" :key="'empty-' + entry.table.id">
                            <div class="border-b border-border">
                                <p class="bg-muted/30 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground" x-text="entry.table.label"></p>
                                <ul>
                                    <template x-for="seat in entry.seats" :key="entry.table.id + '-e-' + seat.index">
                                        <li>
                                            <button
                                                type="button"
                                                class="w-full px-4 py-3 text-left text-sm hover:bg-accent"
                                                x-on:click="assignGuestToSeat(entry.table.id, seat.index)"
                                                x-text="seatLabelPrefix + ' ' + (seat.index + 1)"
                                            ></button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!assignGuestTarget">
                    <div class="min-h-0 flex-1 overflow-hidden">
                        <template x-if="chairModal.currentGuestId">
                            <div class="flex items-center justify-between bg-primary/10 px-4 py-2">
                                <span class="text-sm" x-text="currentGuestName"></span>
                                <x-dashboard.button type="button" variant="destructive" class="!px-2 !py-1 text-xs" x-on:click="clearSeat()">
                                    {{ __('seating.remove_guest') }}
                                </x-dashboard.button>
                            </div>
                        </template>

                        <div class="border-b border-border px-4 py-2">
                            <input
                                type="search"
                                x-model="chairModal.search"
                                class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm"
                                placeholder="{{ __('seating.search_guest') }}"
                                x-init="$el.focus()"
                            />
                        </div>

                        <ul class="max-h-64 overflow-y-auto py-1 sm:max-h-48">
                            <template x-for="guest in filteredGuests" :key="guest.id">
                                <li
                                    class="cursor-pointer px-4 py-2.5 text-sm hover:bg-accent"
                                    x-on:click="assignGuest(guest.id)"
                                >
                                    <div
                                        class="font-medium"
                                        :class="{
                                            'text-rose-600 dark:text-rose-400': guest.is_couple,
                                            'text-muted-foreground': (guest.is_plus_one || guest.is_child) && !guest.is_couple,
                                        }"
                                        x-text="guest.name"
                                    ></div>
                                    <template x-if="guest.labels?.length">
                                        <div class="mt-0.5 text-xs text-muted-foreground" x-text="guest.labels.join(' · ')"></div>
                                    </template>
                                </li>
                            </template>
                            <template x-if="filteredGuests.length === 0">
                                <li class="px-4 py-2 text-sm text-muted-foreground">{{ __('seating.no_guests_available') }}</li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

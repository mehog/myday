<x-filament-panels::page>
    @vite(['resources/js/seating-plan.js'])

    <div
        class="-mx-4 -mb-6 flex h-[calc(100vh-8rem)] flex-col overflow-hidden sm:-mx-6 lg:-mx-8"
        x-data="{
            selectedTable: null,
            inspectorOpen: false,
            inspectorCollapsed: false,
            zoomLabel: '100%',
            seatCount: { assigned: 0, total: 0 },
            allGuests: @js($this->getGuests()),
            chairModal: {
                open: false,
                tableId: null,
                seatIndex: null,
                currentGuestId: null,
                search: '',
            },
            init() {
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
                this.seatCount.total = this.allGuests.length;
                this.seatCount.assigned = window.seatingPlanEditor.getAssignedIds().length;
            },
            openChairModal(data) {
                this.chairModal = {
                    open: true,
                    search: '',
                    tableId: data.tableId,
                    seatIndex: data.seatIndex,
                    currentGuestId: data.guestId,
                };
            },
            closeChairModal() {
                this.chairModal.open = false;
            },
            assignGuest(guestId) {
                window.seatingPlanEditor?.assignToSeat(
                    this.chairModal.tableId,
                    this.chairModal.seatIndex,
                    guestId,
                );
                this.closeChairModal();
            },
            clearSeat() {
                window.seatingPlanEditor?.clearSeat(
                    this.chairModal.tableId,
                    this.chairModal.seatIndex,
                );
                this.closeChairModal();
            },
            get filteredGuests() {
                const query = this.chairModal.search.toLowerCase();
                const assignedIds = window.seatingPlanEditor?.getAssignedIds() ?? [];

                return this.allGuests.filter((guest) =>
                    !assignedIds.includes(guest.id) &&
                    guest.name.toLowerCase().includes(query),
                );
            },
            get currentGuestName() {
                if (!this.chairModal.currentGuestId) {
                    return '';
                }

                return this.allGuests.find((guest) => guest.id === this.chairModal.currentGuestId)?.name ?? '';
            },
            inspectorLabel: '',
            inspectorChairCount: 0,
            inspectorRotation: 0,
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
        }"
        x-effect="syncInspector()"
        x-on:seating-zoom-changed.window="zoomLabel = $event.detail.label"
        x-on:seating-seats-changed.window="seatCount.assigned = $event.detail.assigned"
    >
        {{-- Toolbar --}}
        <div class="flex shrink-0 flex-wrap items-end gap-4 border-b border-gray-200 bg-white px-4 py-2 dark:border-white/10 dark:bg-gray-900">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    {{ __('seating.select_table_type') }}
                </span>
                <div class="flex items-center gap-2">
                    <x-filament::button size="xs sm:sm text-xs sm:text-sm" color="gray" x-on:click="window.seatingPlanEditor?.addTable('round')">
                        {{ __('seating.add_round') }}
                    </x-filament::button>
                    <x-filament::button size="xs sm:sm text-xs sm:text-sm" color="gray" x-on:click="window.seatingPlanEditor?.addTable('rect')">
                        {{ __('seating.add_rect') }}
                    </x-filament::button>
                    <x-filament::button size="xs sm:sm text-xs sm:text-sm" color="gray" x-on:click="window.seatingPlanEditor?.addTable('head')">
                        {{ __('seating.add_head') }}
                    </x-filament::button>
                </div>
            </div>

            <div class="ml-auto flex flex-col gap-1">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 text-right">
                    {{ __('seating.zoom_controls') }}
                </span>
                <div class="flex items-center gap-1">
                    <x-filament::icon-button
                        icon="heroicon-o-minus"
                        size="xs sm:sm"
                        color="gray"
                        :label="__('seating.zoom_out')"
                        x-on:click="window.seatingPlanEditor?.zoomOut()"
                    />
                    <span class="min-w-[3rem] text-center text-sm text-gray-600 dark:text-gray-300" x-text="zoomLabel"></span>
                    <x-filament::icon-button
                        icon="heroicon-o-plus"
                        size="xs sm:sm"
                        color="gray"
                        :label="__('seating.zoom_in')"
                        x-on:click="window.seatingPlanEditor?.zoomIn()"
                    />
                    <x-filament::button size="xs sm:sm text-xs sm:text-sm" color="gray" x-on:click="window.seatingPlanEditor?.resetZoom()">
                        {{ __('seating.reset_zoom') }}
                    </x-filament::button>
                </div>
            </div>
        </div>

        <div class="flex min-h-0 flex-1">
            {{-- Canvas --}}
            <div class="relative min-w-0 flex-1 bg-[#f8f9fb] dark:bg-gray-950">
                <div
                    x-ref="canvasContainer"
                    class="h-full w-full"
                    wire:ignore
                ></div>

                {{-- Floating inspector --}}
                <div class="absolute right-3 top-3 z-10 w-64">
                    <div
                        class="flex items-center justify-between border border-gray-200 bg-white px-3 py-2 shadow-lg dark:border-white/10 dark:bg-gray-900"
                        :class="inspectorCollapsed ? 'rounded-xl' : 'rounded-t-xl'"
                    >
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ __('seating.inspector_heading') }}
                        </span>
                        <button
                            type="button"
                            class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-gray-200"
                            x-on:click="inspectorCollapsed = !inspectorCollapsed"
                        >
                            <svg x-show="!inspectorCollapsed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                            </svg>
                            <svg x-show="inspectorCollapsed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>

                    <div
                        x-show="!inspectorCollapsed"
                        x-transition
                        class="max-h-[calc(100vh-12rem)] overflow-y-auto rounded-b-xl border-x border-b border-gray-200 bg-white shadow-lg dark:border-white/10 dark:bg-gray-900"
                    >
                        <template x-if="inspectorOpen">
                            <div class="flex flex-col gap-4 p-4">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('seating.table_label') }}
                                    </label>
                                    <input
                                        type="text"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-white"
                                        x-model="inspectorLabel"
                                        x-on:change="updateLabel()"
                                        x-on:blur="updateLabel()"
                                    />
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('seating.chairs') }}
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <x-filament::icon-button
                                            icon="heroicon-o-minus"
                                            size="sm"
                                            color="gray"
                                            :label="__('seating.remove_chair')"
                                            x-on:click="removeChair()"
                                        />
                                        <span class="min-w-[2rem] text-center text-lg font-semibold text-gray-900 dark:text-white" x-text="inspectorChairCount"></span>
                                        <x-filament::icon-button
                                            icon="heroicon-o-plus"
                                            size="sm"
                                            color="gray"
                                            :label="__('seating.add_chair')"
                                            x-on:click="addChair()"
                                        />
                                    </div>
                                </div>

                                <template x-if="selectedTable && selectedTable.type !== 'round'">
                                    <div>
                                        <label class="mb-2 block text-xs font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('seating.rotation') }}
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <x-filament::icon-button
                                                icon="heroicon-o-arrow-uturn-left"
                                                size="sm"
                                                color="gray"
                                                :label="__('seating.rotate_left')"
                                                x-on:click="rotateTable(-15)"
                                            />
                                            <span class="min-w-[3rem] text-center text-sm font-semibold text-gray-900 dark:text-white" x-text="inspectorRotation + '°'"></span>
                                            <x-filament::icon-button
                                                icon="heroicon-o-arrow-uturn-right"
                                                size="sm"
                                                color="gray"
                                                :label="__('seating.rotate_right')"
                                                x-on:click="rotateTable(15)"
                                            />
                                            <x-filament::button
                                                size="xs"
                                                color="gray"
                                                x-on:click="rotateTable(-inspectorRotation)"
                                            >
                                                {{ __('seating.reset_rotation') }}
                                            </x-filament::button>
                                        </div>
                                    </div>
                                </template>

                                <div>
                                    <x-filament::button
                                        size="sm"
                                        color="danger"
                                        class="w-full"
                                        x-on:click="deleteTable()"
                                    >
                                        {{ __('seating.delete_table') }}
                                    </x-filament::button>
                                </div>
                            </div>
                        </template>

                        <template x-if="!inspectorOpen">
                            <div class="flex flex-col gap-4 p-4">
                                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('seating.select_table') }}
                                </p>
                                <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-white/10">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('seating.seats_label') }}
                                    </span>
                                    <span
                                        class="text-sm font-semibold text-gray-900 dark:text-white"
                                        x-text="seatCount.assigned + ' / ' + seatCount.total"
                                    ></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <template x-if="chairModal.open">
            <div
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                x-on:keydown.escape.window="closeChairModal()"
            >
                <div class="absolute inset-0 bg-black/50" x-on:click="closeChairModal()"></div>

                <div class="relative z-10 w-full max-w-sm rounded-xl border border-gray-200 bg-white shadow-xl dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ __('seating.assign_guest') }}
                        </span>
                        <button
                            type="button"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            x-on:click="closeChairModal()"
                        >
                            &times;
                        </button>
                    </div>

                    <template x-if="chairModal.currentGuestId">
                        <div class="flex items-center justify-between bg-primary-50 px-4 py-2 dark:bg-primary-500/10">
                            <span class="text-sm text-primary-800 dark:text-primary-200" x-text="currentGuestName"></span>
                            <x-filament::button size="xs" color="danger" x-on:click="clearSeat()">
                                {{ __('seating.remove_guest') }}
                            </x-filament::button>
                        </div>
                    </template>

                    <div class="border-b border-gray-200 px-4 py-2 dark:border-white/10">
                        <input
                            type="text"
                            x-model="chairModal.search"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-white"
                            placeholder="{{ __('seating.search_guest') }}"
                            x-init="$el.focus()"
                        />
                    </div>

                    <ul class="max-h-48 overflow-y-auto py-1">
                        <template x-for="guest in filteredGuests" :key="guest.id">
                            <li
                                class="cursor-pointer px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5"
                                :class="{
                                    'text-rose-600 dark:text-rose-400 font-medium': guest.is_couple,
                                    'text-gray-500 dark:text-gray-400': guest.is_plus_one && !guest.is_couple,
                                    'text-gray-900 dark:text-white': !guest.is_plus_one && !guest.is_couple,
                                }"
                                x-on:click="assignGuest(guest.id)"
                                x-text="guest.name"
                            ></li>
                        </template>
                        <template x-if="filteredGuests.length === 0">
                            <li class="px-4 py-2 text-sm text-gray-400 dark:text-gray-500">
                                {{ __('seating.no_guests_available') }}
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </template>
    </div>
</x-filament-panels::page>

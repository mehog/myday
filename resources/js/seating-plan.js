import Konva from 'konva';

const COLORS = {
    tableFill: '#f5f0e8',
    tableStroke: '#c9a227',
    chairEmpty: '#d1d5db',
    chairAssigned: '#c9a227',
    chairCouple: '#f43f5e',
    chairStroke: '#9ca3af',
    selection: '#3b82f6',
    handle: '#3b82f6',
    grid: '#e5e7eb',
    label: '#374151',
};

const CHAIR_RADIUS = 12;
const CHAIR_OFFSET = 22;
const RECT_CHAIR_INSET = 28;
const MIN_RECT_WIDTH = 80;
const MIN_ROUND_RADIUS = 30;
const HANDLE_WIDTH = 8;
const HANDLE_HEIGHT = 32;
const LABEL_WIDTH = 200;
const LABEL_HEIGHT = 10;

function generateId() {
    return `t_${Math.random().toString(36).slice(2, 10)}`;
}

function deepClone(value) {
    return JSON.parse(JSON.stringify(value));
}

function truncateName(name, max) {
    if (!name || name.length <= max) {
        return name;
    }

    return `${name.slice(0, max - 1)}…`;
}

function defaultTable(type, index, centerX, centerY) {
    const labelBase = type === 'head' ? 'Glavni sto' : `Sto ${index}`;

    if (type === 'round') {
        return {
            id: generateId(),
            type: 'round',
            x: centerX,
            y: centerY,
            radius: 70,
            rotation: 0,
            chair_count: 8,
            label: labelBase,
            seats: Array(8).fill(null),
        };
    }

    if (type === 'head') {
        return {
            id: generateId(),
            type: 'head',
            x: centerX,
            y: centerY,
            width: 320,
            height: 70,
            rotation: 0,
            chair_count: 10,
            label: labelBase,
            seats: Array(10).fill(null),
        };
    }

    return {
        id: generateId(),
        type: 'rect',
        x: centerX,
        y: centerY,
        width: 160,
        height: 100,
        rotation: 0,
        chair_count: 8,
        label: labelBase,
        seats: Array(8).fill(null),
    };
}

function getAssignedGuestIds(plan) {
    return plan.tables.flatMap((table) => table.seats.filter(Boolean));
}

function getOccupiedCount(table) {
    return table.seats.filter(Boolean).length;
}

function distributeRectChairs(count, width, height) {
    const positions = [];
    if (count === 0) {
        return positions;
    }

    const top = Math.floor(count / 2);
    const bottom = count - top;
    const innerWidth = width - 2 * RECT_CHAIR_INSET;
    const startX = -width / 2 + RECT_CHAIR_INSET;

    for (let i = 0; i < top; i++) {
        const t = top === 1 ? 0.5 : i / (top - 1);
        positions.push({
            x: startX + innerWidth * t,
            y: -height / 2 - CHAIR_OFFSET,
        });
    }

    for (let i = 0; i < bottom; i++) {
        const t = bottom === 1 ? 0.5 : i / (bottom - 1);
        positions.push({
            x: startX + innerWidth * t,
            y: height / 2 + CHAIR_OFFSET,
        });
    }

    return positions;
}

function distributeHeadChairs(count, width, height) {
    const positions = [];
    const spacing = count <= 1 ? 0 : Math.min(36, width / (count - 1));
    const totalWidth = spacing * (count - 1);
    const startX = -totalWidth / 2;

    for (let i = 0; i < count; i++) {
        positions.push({
            x: startX + spacing * i,
            y: -height / 2 - CHAIR_OFFSET,
        });
    }

    return positions;
}

function distributeRoundChairs(count, radius) {
    const positions = [];
    const orbit = radius + CHAIR_OFFSET;

    for (let i = 0; i < count; i++) {
        const angle = (i / count) * Math.PI * 2 - Math.PI / 2;
        positions.push({
            x: Math.cos(angle) * orbit,
            y: Math.sin(angle) * orbit,
        });
    }

    return positions;
}

window.createSeatingPlanEditor = function createSeatingPlanEditor(config) {
    const {
        container,
        wire,
        initialPlan,
        guests,
        labels,
        exportPdfUrl,
        onSelectionChange,
        onChairClick,
    } = config;

    let plan = deepClone(initialPlan?.tables ? initialPlan : { tables: [] });
    let selectedTableId = null;
    let scale = 1;
    let tableCounter = plan.tables.length + 1;
    let savedPlanJson = JSON.stringify(plan);

    const guestMap = new Map(guests.map((guest) => [guest.id, guest.name]));

    const stage = new Konva.Stage({
        container,
        width: container.clientWidth,
        height: container.clientHeight,
        draggable: true,
    });

    const gridLayer = new Konva.Layer({ listening: false });
    const layer = new Konva.Layer();
    stage.add(gridLayer);
    stage.add(layer);

    function drawGrid() {
        gridLayer.destroyChildren();
        const spacing = 20;
        const width = stage.width();
        const height = stage.height();

        for (let x = 0; x <= width; x += spacing) {
            for (let y = 0; y <= height; y += spacing) {
                gridLayer.add(
                    new Konva.Circle({
                        x,
                        y,
                        radius: 1,
                        fill: COLORS.grid,
                    }),
                );
            }
        }

        gridLayer.batchDraw();
    }

    function emitZoom() {
        window.dispatchEvent(
            new CustomEvent('seating-zoom-changed', {
                detail: { label: `${Math.round(scale * 100)}%` },
            }),
        );
    }

    function emitSeats() {
        window.dispatchEvent(
            new CustomEvent('seating-seats-changed', {
                detail: { assigned: getAssignedGuestIds(plan).length },
            }),
        );
    }

    function applyZoom(newScale, anchor) {
        const oldScale = scale;
        scale = newScale;
        stage.scale({ x: scale, y: scale });
        stage.position({
            x: anchor.x - (anchor.x - stage.x()) * (scale / oldScale),
            y: anchor.y - (anchor.y - stage.y()) * (scale / oldScale),
        });
        stage.batchDraw();
        emitZoom();
    }

    function getViewportCenter() {
        return {
            x: stage.width() / 2,
            y: stage.height() / 2,
        };
    }

    function getStageCenterCoords() {
        const pos = stage.position();

        return {
            x: (stage.width() / 2 - pos.x) / scale,
            y: (stage.height() / 2 - pos.y) / scale,
        };
    }

    function isDirty() {
        return JSON.stringify(plan) !== savedPlanJson;
    }

    function save(manual = true) {
        return wire.save(JSON.stringify(plan), manual).then(() => {
            savedPlanJson = JSON.stringify(plan);
        });
    }

    function promptSaveBeforeLeave(onLeave) {
        if (!isDirty()) {
            onLeave();
            return;
        }

        if (window.confirm(labels.unsaved_save_before_leave)) {
            save(false).then(onLeave);
            return;
        }

        if (window.confirm(labels.unsaved_leave_without_saving)) {
            onLeave();
        }
    }

    function handleBeforeUnload(event) {
        if (!isDirty()) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
    }

    function handleLinkClick(event) {
        if (!isDirty()) {
            return;
        }

        const anchor = event.target.closest('a[href]');
        if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('download')) {
            return;
        }

        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
            return;
        }

        let url;
        try {
            url = new URL(href, window.location.href);
        } catch {
            return;
        }

        if (url.origin !== window.location.origin) {
            return;
        }

        if (url.pathname === window.location.pathname && url.search === window.location.search) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        promptSaveBeforeLeave(() => {
            window.location.href = url.href;
        });
    }

    window.addEventListener('beforeunload', handleBeforeUnload);
    document.addEventListener('click', handleLinkClick, true);

    function getTableById(id) {
        return plan.tables.find((table) => table.id === id) ?? null;
    }

    function getSelectedTable() {
        return selectedTableId ? getTableById(selectedTableId) : null;
    }

    function selectTable(id) {
        selectedTableId = id;
        renderAll();
        onSelectionChange?.(getSelectedTable());
    }

    function deselectTable() {
        selectedTableId = null;
        renderAll();
        onSelectionChange?.(null);
    }

    function isGuestAssigned(guestId, excludeTableId = null, excludeSeatIndex = null) {
        return plan.tables.some((table) =>
            table.seats.some((seatGuestId, index) => {
                if (!seatGuestId || seatGuestId !== guestId) {
                    return false;
                }

                if (table.id === excludeTableId && index === excludeSeatIndex) {
                    return false;
                }

                return true;
            }),
        );
    }

    function renderChairs(group, tableData, tableNodeName = 'chairs') {
        const existing = group.findOne(`.${tableNodeName}`);
        existing?.destroy();

        const chairsGroup = new Konva.Group({ name: tableNodeName });
        let positions = [];

        if (tableData.type === 'round') {
            positions = distributeRoundChairs(tableData.chair_count, tableData.radius);
        } else if (tableData.type === 'head') {
            positions = distributeHeadChairs(tableData.chair_count, tableData.width, tableData.height);
        } else {
            positions = distributeRectChairs(tableData.chair_count, tableData.width, tableData.height);
        }

        positions.forEach((position, index) => {
            const guestId = tableData.seats[index] ?? null;
            const assigned = guestId !== null;
            const isCouple = guestId === 'bride' || guestId === 'groom';

            const chair = new Konva.Circle({
                x: position.x,
                y: position.y,
                radius: CHAIR_RADIUS,
                fill: assigned ? (isCouple ? COLORS.chairCouple : COLORS.chairAssigned) : COLORS.chairEmpty,
                stroke: COLORS.chairStroke,
                strokeWidth: 1,
                name: 'chair',
            });

            chair.setAttr('tableId', tableData.id);
            chair.setAttr('seatIndex', index);
            chair.setAttr('guestId', guestId);

            if (assigned) {
                const guestName = guestMap.get(guestId) ?? '';
                const displayName = truncateName(guestName, 40);
                const angle = Math.atan2(position.y, position.x);
                const labelDist = CHAIR_RADIUS + 14;

                chairsGroup.add(
                    new Konva.Text({
                        x: position.x + Math.cos(angle) * labelDist,
                        y: position.y + Math.sin(angle) * labelDist,
                        offsetX: LABEL_WIDTH / 2,
                        offsetY: LABEL_HEIGHT / 2,
                        width: LABEL_WIDTH,
                        align: 'center',
                        rotation: -(tableData.rotation ?? 0),
                        text: displayName,
                        fontSize: 9,
                        fill: COLORS.label,
                        listening: false,
                    }),
                );
            }

            chair.on('click tap', (event) => {
                event.cancelBubble = true;
                onChairClick?.({
                    tableId: tableData.id,
                    seatIndex: index,
                    guestId,
                });
            });

            chairsGroup.add(chair);
        });

        group.add(chairsGroup);
    }

    function createResizeHandles(group, tableData) {
        if (tableData.type === 'round') {
            const handle = new Konva.Circle({
                x: tableData.radius,
                y: 0,
                radius: HANDLE_WIDTH,
                fill: COLORS.handle,
                draggable: true,
                name: 'resize-handle-round',
                visible: tableData.id === selectedTableId,
            });

            handle.on('dragstart', () => group.draggable(false));

            handle.on('dragmove', () => {
                const newRadius = Math.max(MIN_ROUND_RADIUS, handle.x());
                tableData.radius = newRadius;
                handle.x(newRadius);
                handle.y(0);
                renderChairs(group, tableData);
                updateTableShape(group, tableData);
            });

            handle.on('dragend', () => {
                group.draggable(true);
            });

            group.add(handle);

            return;
        }

        const width = tableData.width;
        const height = tableData.height;

        const leftHandle = new Konva.Rect({
            x: -width / 2 - HANDLE_WIDTH / 2,
            y: -HANDLE_HEIGHT / 2,
            width: HANDLE_WIDTH,
            height: HANDLE_HEIGHT,
            fill: COLORS.handle,
            cornerRadius: 3,
            draggable: true,
            name: 'resize-handle-left',
            visible: tableData.id === selectedTableId,
        });

        const rightHandle = new Konva.Rect({
            x: width / 2 - HANDLE_WIDTH / 2,
            y: -HANDLE_HEIGHT / 2,
            width: HANDLE_WIDTH,
            height: HANDLE_HEIGHT,
            fill: COLORS.handle,
            cornerRadius: 3,
            draggable: true,
            name: 'resize-handle-right',
            visible: tableData.id === selectedTableId,
        });

        leftHandle.on('dragstart', () => group.draggable(false));
        rightHandle.on('dragstart', () => group.draggable(false));

        leftHandle.on('dragmove', () => {
            const delta = leftHandle.x() - (-width / 2 - HANDLE_WIDTH / 2);
            const newWidth = Math.max(MIN_RECT_WIDTH, width - delta * 2);
            tableData.width = newWidth;
            renderChairs(group, tableData);
            updateTableShape(group, tableData);
            repositionResizeHandles(group, tableData);
            leftHandle.x(-newWidth / 2 - HANDLE_WIDTH / 2);
        });

        rightHandle.on('dragmove', () => {
            const delta = rightHandle.x() - (width / 2 - HANDLE_WIDTH / 2);
            const newWidth = Math.max(MIN_RECT_WIDTH, width + delta * 2);
            tableData.width = newWidth;
            renderChairs(group, tableData);
            updateTableShape(group, tableData);
            repositionResizeHandles(group, tableData);
            rightHandle.x(newWidth / 2 - HANDLE_WIDTH / 2);
        });

        const endResize = () => {
            group.draggable(true);
        };

        leftHandle.on('dragend', endResize);
        rightHandle.on('dragend', endResize);

        group.add(leftHandle);
        group.add(rightHandle);
    }

    function repositionResizeHandles(group, tableData) {
        if (tableData.type === 'round') {
            const handle = group.findOne('.resize-handle-round');
            if (handle) {
                handle.x(tableData.radius);
                handle.visible(tableData.id === selectedTableId);
            }

            return;
        }

        const left = group.findOne('.resize-handle-left');
        const right = group.findOne('.resize-handle-right');

        if (!left || !right) {
            return;
        }

        left.x(-tableData.width / 2 - HANDLE_WIDTH / 2);
        right.x(tableData.width / 2 - HANDLE_WIDTH / 2);
        left.visible(tableData.id === selectedTableId);
        right.visible(tableData.id === selectedTableId);
    }

    function updateTableShape(group, tableData) {
        const shape = group.findOne('.table-shape');
        const label = group.findOne('.table-label');
        const occupancy = group.findOne('.table-occupancy');

        if (!shape) {
            return;
        }

        if (tableData.type === 'round') {
            shape.radius(tableData.radius);
        } else {
            shape.width(tableData.width);
            shape.height(tableData.height);
            shape.x(-tableData.width / 2);
            shape.y(-tableData.height / 2);
        }

        if (label) {
            label.text(tableData.label);
            centerLabel(group, tableData, label, occupancy);
        }
    }

    function centerLabel(group, tableData, label, occupancy) {
        const bounds = group.getClientRect({ skipTransform: true });
        const centerX = bounds.x + bounds.width / 2;
        const labelY = bounds.y + bounds.height / 2 - 10;
        const occupancyY = bounds.y + bounds.height / 2 + 6;

        label.x(centerX - label.width() / 2);
        label.y(labelY);

        if (occupancy) {
            occupancy.text(`${getOccupiedCount(tableData)}/${tableData.chair_count}`);
            occupancy.x(centerX - occupancy.width() / 2);
            occupancy.y(occupancyY);
        }
    }

    function createTableGroup(tableData) {
        const group = new Konva.Group({
            x: tableData.x,
            y: tableData.y,
            rotation: tableData.rotation ?? 0,
            draggable: true,
            name: 'table-group',
        });

        group.setAttr('tableId', tableData.id);

        let shape;

        if (tableData.type === 'round') {
            shape = new Konva.Circle({
                x: 0,
                y: 0,
                radius: tableData.radius,
                fill: COLORS.tableFill,
                stroke: tableData.id === selectedTableId ? COLORS.selection : COLORS.tableStroke,
                strokeWidth: tableData.id === selectedTableId ? 3 : 2,
                name: 'table-shape',
            });
        } else {
            shape = new Konva.Rect({
                x: -tableData.width / 2,
                y: -tableData.height / 2,
                width: tableData.width,
                height: tableData.height,
                fill: COLORS.tableFill,
                stroke: tableData.id === selectedTableId ? COLORS.selection : COLORS.tableStroke,
                strokeWidth: tableData.id === selectedTableId ? 3 : 2,
                cornerRadius: 8,
                name: 'table-shape',
            });
        }

        const label = new Konva.Text({
            text: tableData.label,
            fontSize: 13,
            fontStyle: 'bold',
            fill: COLORS.label,
            name: 'table-label',
            listening: false,
        });

        const occupancy = new Konva.Text({
            text: `${getOccupiedCount(tableData)}/${tableData.chair_count}`,
            fontSize: 11,
            fill: COLORS.label,
            name: 'table-occupancy',
            listening: false,
        });

        group.add(shape);
        group.add(label);
        group.add(occupancy);
        renderChairs(group, tableData);
        createResizeHandles(group, tableData);
        centerLabel(group, tableData, label, occupancy);

        group.on('dragend', () => {
            tableData.x = group.x();
            tableData.y = group.y();
        });

        group.on('click tap', (event) => {
            event.cancelBubble = true;
            selectTable(tableData.id);
        });

        return group;
    }

    function renderAll() {
        layer.destroyChildren();

        plan.tables.forEach((tableData) => {
            layer.add(createTableGroup(tableData));
        });

        layer.batchDraw();
    }

    function resizeStage() {
        stage.width(container.clientWidth);
        stage.height(container.clientHeight);
        drawGrid();
        stage.batchDraw();
    }

    function assignGuestToSeat(tableId, seatIndex, guestId) {
        const table = getTableById(tableId);
        if (!table) {
            return false;
        }

        if (isGuestAssigned(guestId, tableId, seatIndex)) {
            window.alert(labels.duplicate_guest);
            return false;
        }

        while (table.seats.length < table.chair_count) {
            table.seats.push(null);
        }

        table.seats[seatIndex] = guestId;
        renderAll();
        emitSeats();
        return true;
    }

    function handleStageClick(event) {
        const clickedOnEmpty = event.target === stage || event.target.getParent() === gridLayer;
        if (clickedOnEmpty) {
            deselectTable();
        }
    }

    stage.on('click tap', handleStageClick);

    stage.on('wheel', (event) => {
        event.evt.preventDefault();

        const pointer = stage.getPointerPosition();
        if (!pointer) {
            return;
        }

        const direction = event.evt.deltaY > 0 ? -1 : 1;
        const newScale = Math.min(3, Math.max(0.3, scale + direction * 0.1));
        applyZoom(newScale, pointer);
    });

    const resizeObserver = new ResizeObserver(() => resizeStage());
    resizeObserver.observe(container);

    drawGrid();
    renderAll();
    emitZoom();
    emitSeats();

    const api = {
        addTable(type) {
            const { x: centerX, y: centerY } = getStageCenterCoords();
            const table = defaultTable(type, tableCounter++, centerX, centerY);
            plan.tables.push(table);
            selectTable(table.id);
        },

        updateSelectedLabel(label) {
            const table = getSelectedTable();
            if (!table) {
                return;
            }

            table.label = label;
            renderAll();
        },

        addChairToSelected() {
            const table = getSelectedTable();
            if (!table) {
                return;
            }

            table.chair_count += 1;
            table.seats.push(null);
            renderAll();
            onSelectionChange?.(table);
        },

        removeChairFromSelected() {
            const table = getSelectedTable();
            if (!table || table.chair_count <= 0) {
                return;
            }

            const lastSeat = table.seats[table.chair_count - 1];
            if (lastSeat && !window.confirm(labels.remove_chair_confirm)) {
                return;
            }

            table.chair_count -= 1;
            table.seats.pop();
            renderAll();
            onSelectionChange?.(table);
        },

        deleteSelectedTable() {
            if (!selectedTableId) {
                return;
            }

            plan.tables = plan.tables.filter((table) => table.id !== selectedTableId);
            deselectTable();
            emitSeats();
        },

        rotateSelected(degrees) {
            const table = getSelectedTable();
            if (!table || table.type === 'round') {
                return;
            }

            table.rotation = ((table.rotation ?? 0) + degrees + 360) % 360;
            renderAll();
            onSelectionChange?.(table);
        },

        assignToSeat(tableId, seatIndex, guestId) {
            assignGuestToSeat(tableId, seatIndex, guestId);
        },

        clearSeat(tableId, seatIndex) {
            const table = getTableById(tableId);
            if (!table) {
                return;
            }

            table.seats[seatIndex] = null;
            renderAll();
            emitSeats();
        },

        getAssignedIds() {
            return getAssignedGuestIds(plan);
        },

        save,

        isDirty,

        exportPdf() {
            const exportButton = document.getElementById('seating-export-pdf-btn');
            if (exportButton?.disabled) {
                return;
            }

            if (exportButton) {
                exportButton.disabled = true;
            }

            const exportTab = window.open('', '_blank');

            const savedScale = scale;
            const savedPos = stage.position();
            const savedW = stage.width();
            const savedH = stage.height();
            const savedSelectedId = selectedTableId;

            scale = 1;
            stage.scale({ x: 1, y: 1 });
            stage.position({ x: 0, y: 0 });
            selectedTableId = null;
            renderAll();
            layer.draw();

            const padding = 40;
            const rect = layer.getClientRect();
            const contentW = Math.ceil(rect.width + padding * 2);
            const contentH = Math.ceil(rect.height + padding * 2);

            stage.width(contentW);
            stage.height(contentH);
            stage.position({ x: -rect.x + padding, y: -rect.y + padding });
            gridLayer.hide();

            layer.destroyChildren();
            plan.tables.forEach((tableData) => layer.add(createTableGroup(tableData)));
            layer.draw();

            const dataUrl = stage.toDataURL({
                x: 0,
                y: 0,
                width: contentW,
                height: contentH,
                pixelRatio: 2,
            });

            stage.width(savedW);
            stage.height(savedH);
            scale = savedScale;
            stage.scale({ x: savedScale, y: savedScale });
            stage.position(savedPos);
            gridLayer.show();
            selectedTableId = savedSelectedId;
            drawGrid();
            renderAll();
            emitZoom();

            wire.exportPdf(dataUrl)
                .then(() => {
                    if (exportTab) {
                        exportTab.location.href = exportPdfUrl;
                    } else {
                        window.open(exportPdfUrl, '_blank');
                    }
                })
                .catch(() => {
                    exportTab?.close();
                })
                .finally(() => {
                    if (exportButton) {
                        exportButton.disabled = false;
                    }
                });
        },

        zoomIn() {
            applyZoom(Math.min(scale + 0.1, 3), getViewportCenter());
        },

        zoomOut() {
            applyZoom(Math.max(scale - 0.1, 0.3), getViewportCenter());
        },

        resetZoom() {
            scale = 1;
            stage.scale({ x: 1, y: 1 });
            stage.position({ x: 0, y: 0 });
            stage.batchDraw();
            emitZoom();
        },

        getZoomLabel() {
            return `${Math.round(scale * 100)}%`;
        },
    };

    window.seatingPlanEditor = api;

    return api;
};

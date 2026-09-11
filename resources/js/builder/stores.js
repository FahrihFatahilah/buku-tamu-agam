import Alpine from 'alpinejs';
import ops from './document-ops';

/**
 * Editor state, deliberately split into small stores rather than one giant
 * Alpine.data('builder', ...) component.
 *
 *   builder   document + persistence
 *   ui        device, panel tabs, view options
 *   selection hovered/selected node
 *   history   undo/redo snapshots
 *   clipboard copy/paste buffers
 */
export function registerStores(config) {
    const { document: initialDocument, registry, weddingId, urls } = config;

    // ── Document ───────────────────────────────────────────────────────────
    Alpine.store('builder', {
        document: ops.cloneDocument(initialDocument),
        registry,
        weddingId,
        urls,
        dirty: false,
        saving: false,
        lastSavedAt: null,
        saveErrors: [],

        init() {
            Alpine.store('history').reset(this.document);
        },

        /** Replace the whole document and checkpoint history. */
        replace(document, { checkpoint = true, markDirty = true } = {}) {
            this.document = document;
            if (checkpoint) Alpine.store('history').push(document);
            if (markDirty) this.dirty = true;
        },

        /** Apply a document operation from document-ops. */
        apply(fn) {
            const next = fn(this.document);
            this.replace(next);
            return next;
        },

        selected() {
            const id = Alpine.store('selection').selectedId;
            if (!id) return null;
            return ops.findNode(this.document.nodes, id)?.node || null;
        },

        selectedEntry() {
            const id = Alpine.store('selection').selectedId;
            if (!id) return null;
            return ops.findNode(this.document.nodes, id);
        },

        /** Find the registry definition for the current selection. */
        definitionFor(node) {
            if (!node) return null;

            if (node.type === 'decoration') {
                return this.registry.decorations.items.find((d) => d.type === (node.props?.asset || '').replace('builtin:', '')) || null;
            }

            return this.registry.widgets.items.find((w) => w.type === node.type) || null;
        },

        /** What may be dropped inside the current selection? */
        allowedChildrenFor(node) {
            if (!node) return { root: true };
            const definition = this.definitionFor(node);
            const allowed = definition?.allowedChildren;

            return {
                root: false,
                allowed,
                accepts: (childType) => allowed === true
                    || (Array.isArray(allowed) && allowed.includes(childType)),
            };
        },

        /** Where a new node should go: inside the selection if it can contain it. */
        targetParentFor(type) {
            const selection = Alpine.store('selection');

            if (!selection.selectedId) return null;

            const node = this.selected();
            const rules = this.allowedChildrenFor(node);

            return rules.accepts && rules.accepts(type) ? selection.selectedId : null;
        },

        addWidget(type) {
            const definition = this.registry.widgets.items.find((w) => w.type === type);
            if (!definition) return;

            const node = {
                id: ops.makeId('n'),
                type,
                props: JSON.parse(JSON.stringify(definition.defaultProps || {})),
                styles: JSON.parse(JSON.stringify(definition.defaultStyles || {})),
                children: [],
            };

            const parentId = this.targetParentFor(type);
            this.apply((doc) => ops.insertNode(doc, parentId, node));
            Alpine.store('selection').select(node.id);
        },

        addDecoration(assetName) {
            const definition = this.registry.decorations.items.find((d) => d.asset === assetName);
            if (!definition) return;

            const node = {
                id: ops.makeId('deco'),
                type: 'decoration',
                props: { asset: `builtin:${assetName}`, color: '', alt: '' },
                styles: JSON.parse(JSON.stringify(definition.defaultStyles || { desktop: {} })),
                children: [],
            };

            // Decorations belong inside a container so they can be positioned.
            const parentId = this.targetParentFor('decoration') || this.firstContainerId();
            this.apply((doc) => ops.insertNode(doc, parentId, node));
            Alpine.store('selection').select(node.id);
        },

        firstContainerId() {
            const containers = ops.flatNodes(this.document).filter((entry) => {
                const definition = this.definitionFor(entry.node);
                return definition?.allowedChildren === true
                    || Array.isArray(definition?.allowedChildren);
            });

            return containers.length ? containers[0].id : null;
        },

        remove(id) {
            const targetId = id || Alpine.store('selection').selectedId;
            if (!targetId) return;

            this.apply((doc) => ops.removeNode(doc, targetId));

            if (Alpine.store('selection').selectedId === targetId) {
                Alpine.store('selection').clear();
            }
        },

        duplicate(id) {
            const targetId = id || Alpine.store('selection').selectedId;
            if (!targetId) return;

            const next = this.apply((doc) => ops.duplicateNode(doc, targetId));

            // Select the copy (it sits directly after the original).
            const flat = ops.flatNodes(next);
            const index = flat.findIndex((entry) => entry.id === targetId);

            if (index !== -1 && flat[index + 1]) {
                Alpine.store('selection').select(flat[index + 1].id);
            }
        },

        toggleFlag(id, key) {
            const entry = ops.findNode(this.document.nodes, id);
            if (!entry) return;

            this.apply((doc) => ops.setNodeFlag(doc, id, key, !entry.node[key]));
        },

        rename(id, name) {
            this.apply((doc) => ops.setNodeFlag(doc, id, 'name', name));
        },

        addOverlay(type) {
            this.apply((doc) => ops.addOverlay(doc, type));
        },

        removeOverlay(id) {
            this.apply((doc) => ops.removeOverlay(doc, id));
        },

        updateOverlay(id, patch) {
            this.apply((doc) => ops.updateOverlay(doc, id, patch));
        },

        setTheme(patch) {
            this.apply((doc) => ops.updateTheme(doc, patch));
        },

        // ── Inspector read/write ───────────────────────────────────────────
        // Field partials call these so each control does not need to know how
        // props and per-breakpoint styles differ.

        readField(node, name, target = 'props') {
            if (!node) return '';

            if (target === 'styles') {
                const device = Alpine.store('ui').device;
                return node.styles?.[device]?.[name]
                    ?? node.styles?.desktop?.[name]
                    ?? '';
            }

            return node.props?.[name] ?? '';
        },

        writeField(id, name, value, target = 'props', { refresh = true, checkpoint = true } = {}) {
            const device = Alpine.store('ui').device;

            const next = target === 'styles'
                ? ops.setStyle(this.document, id, device, name, value)
                : ops.setProp(this.document, id, name, value);

            this.replace(next, { checkpoint });

            if (refresh) this.scheduleRefresh();
        },

        /**
         * Re-render the canvas after a short idle. Keystroke-by-keystroke
         * round-trips would make the editor feel laggy.
         */
        scheduleRefresh(delay = 450) {
            clearTimeout(this.refreshTimer);
            this.refreshTimer = setTimeout(() => this.refreshCanvas(), delay);
        },

        refreshTimer: null,

        // ── Generic field access ───────────────────────────────────────────
        // The inspector's field partials all go through these two, so one
        // control implementation works for props, styles and overlays.

        overlaySelected() {
            const id = Alpine.store('selection').selectedId;
            if (!id || !String(id).startsWith('overlay:')) return null;

            const type = String(id).slice(8);
            return (this.document.overlays || []).find((o) => o.type === type) || null;
        },

        fieldValue(name, target = 'props') {
            if (target === 'overlay-props') {
                return this.overlaySelected()?.props?.[name] ?? '';
            }

            return this.readField(this.selected(), name, target);
        },

        fieldWrite(name, value, target = 'props') {
            if (target === 'overlay-props') {
                const overlay = this.overlaySelected();
                if (!overlay) return;

                return this.updateOverlay(overlay.id, { props: { ...(overlay.props || {}), [name]: value } });
            }

            return this.writeField(Alpine.store('selection').selectedId, name, value, target);
        },

        overlayEnabled() {
            return !!this.overlaySelected()?.enabled;
        },

        overlayMobile() {
            return !!this.overlaySelected()?.mobile;
        },

        /** Node type of the current selection (used to reveal field sets). */
        nodeType() {
            return this.selected()?.type || null;
        },

        /** Human label for the current selection. */
        selectionLabel() {
            const overlay = this.overlaySelected();
            if (overlay) {
                const def = (this.registry.overlays.items || []).find((o) => o.type === overlay.type);
                return def ? def.name : overlay.type;
            }

            const node = this.selected();
            if (!node) return '';

            if (node.type === 'decoration') return 'Dekorasi';

            const def = (this.registry.widgets.items || []).find((w) => w.type === node.type);
            return def ? def.name : node.type;
        },

        /** Animation groups relevant to a trigger, for the picker. */
        animationOptionsFor(trigger) {
            const map = { entrance: 'entrance', continuous: 'continuous', scroll: 'scroll' };
            const group = (this.registry.animationGroups || {})[map[trigger]] || [];
            return group;
        },

        defaultAnimationFor(trigger) {
            return { entrance: 'fade-up', continuous: 'float', scroll: 'parallax' }[trigger] || 'fade';
        },

        nodeAnimation(trigger) {
            return this.selected()?.animation?.[trigger] || null;
        },

        setAnimationType(trigger, type) {
            const id = Alpine.store('selection').selectedId;
            if (!id) return;

            this.apply((doc) => ops.setAnimation(doc, id, trigger, {
                type,
                duration: trigger === 'continuous' ? 4000 : 700,
                delay: 0,
            }));

            this.scheduleRefresh(0);
        },

        patchAnimation(trigger, patch) {
            const id = Alpine.store('selection').selectedId;
            if (!id) return;

            const current = this.nodeAnimation(trigger);
            if (!current) return;

            this.apply((doc) => ops.setAnimation(doc, id, trigger, { ...current, ...patch }));
        },

        clearAnimation(trigger) {
            const id = Alpine.store('selection').selectedId;
            if (!id) return;

            this.apply((doc) => ops.setAnimation(doc, id, trigger, null));
            this.scheduleRefresh(0);
        },

        /** Stage the document and reload the canvas iframe. */
        async refreshCanvas() {
            const frame = document.getElementById('builder-canvas');
            if (!frame) return;

            try {
                await fetch(this.urls.stage, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ document: this.document }),
                });
            } catch (e) {
                // The canvas just keeps the previous render.
            }

            frame.src = `${this.urls.preview}?t=${Date.now()}`;
        },

        async save() {
            this.saving = true;
            this.saveErrors = [];

            try {
                const response = await fetch(this.urls.save, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify(this.document),
                });

                const data = await response.json();

                if (data.document) {
                    // Adopt the server's sanitised document.
                    this.document = data.document;
                    Alpine.store('history').reset(data.document);
                }

                this.saveErrors = Object.values(data.errors || {}).flat();
                this.dirty = false;
                this.lastSavedAt = new Date();
            } catch (e) {
                this.saveErrors = ['Gagal menyimpan. Periksa koneksi Anda.'];
            } finally {
                this.saving = false;
            }
        },

        async enable() {
            const response = await fetch(this.urls.enable, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    Accept: 'application/json',
                },
            });

            const data = await response.json();

            if (data.document) {
                this.document = data.document;
                Alpine.store('history').reset(data.document);
            }
        },

        async publish() {
            const response = await fetch(this.urls.publish, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    Accept: 'application/json',
                },
            });

            const data = await response.json();
            this.dirty = false;

            return data;
        },

        async revert() {
            await fetch(this.urls.revert, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    Accept: 'application/json',
                },
            });

            window.location.reload();
        },
    });

    // ── Selection ──────────────────────────────────────────────────────────
    Alpine.store('selection', {
        selectedId: null,
        hoveredId: null,

        select(id) {
            this.selectedId = id;
            Alpine.store('ui').inspectorTab = 'content';
        },

        clear() {
            this.selectedId = null;
        },

        hover(id) {
            this.hoveredId = id;
        },
    });

    // ── UI ─────────────────────────────────────────────────────────────────
    Alpine.store('ui', {
        device: 'desktop',
        leftTab: 'elements',
        inspectorTab: 'content',
        panelsOpen: true,
        deviceWidths: { desktop: '100%', tablet: '768px', mobile: '390px' },
        deviceHeights: { desktop: '100%', tablet: '900px', mobile: '780px' },
        search: '',

        get frameWidth() {
            return this.panelsOpen ? this.deviceWidths[this.device] : this.deviceWidths[this.device];
        },

        get frameHeight() {
            return this.deviceHeights[this.device];
        },

        /** Element palette filtered by the current search. */
        get filteredGroups() {
            const term = this.search.trim().toLowerCase();
            const groups = Alpine.store('builder').registry.widgets.groups || [];

            if (!term) return groups;

            return groups
                .map((group) => ({
                    ...group,
                    items: group.items.filter((item) =>
                        item.name.toLowerCase().includes(term)
                        || item.type.toLowerCase().includes(term)),
                }))
                .filter((group) => group.items.length);
        },
    });

    // ── History ────────────────────────────────────────────────────────────
    // A stack of full document snapshots with a cursor. Snapshots (not diffs)
    // because documents are small and correctness matters more than memory
    // here; the stack is capped.
    Alpine.store('history', {
        stack: [],
        index: -1,
        limit: 60,

        reset(document) {
            this.stack = [ops.cloneDocument(document)];
            this.index = 0;
        },

        push(document) {
            // Drop any redo branch.
            this.stack = this.stack.slice(0, this.index + 1);

            // Skip no-op checkpoints so undo does not stall on identical states.
            if (ops.signature(this.stack[this.index]) === ops.signature(document)) {
                return;
            }

            this.stack.push(ops.cloneDocument(document));

            if (this.stack.length > this.limit) {
                this.stack.shift();
            }

            this.index = this.stack.length - 1;
        },

        get canUndo() {
            return this.index > 0;
        },

        get canRedo() {
            return this.index < this.stack.length - 1;
        },

        undo() {
            if (!this.canUndo) return;
            this.index -= 1;
            Alpine.store('builder').document = ops.cloneDocument(this.stack[this.index]);
        },

        redo() {
            if (!this.canRedo) return;
            this.index += 1;
            Alpine.store('builder').document = ops.cloneDocument(this.stack[this.index]);
        },
    });

    // ── Clipboard ──────────────────────────────────────────────────────────
    Alpine.store('clipboard', {
        node: null,
        styles: null,

        copyNode(node) {
            if (node) this.node = JSON.parse(JSON.stringify(node));
        },

        pasteNode() {
            if (!this.node) return;
            Alpine.store('builder').addWidget(this.node.type);
        },

        copyStyles(node) {
            if (node?.styles) this.styles = JSON.parse(JSON.stringify(node.styles));
        },

        pasteStyles(node) {
            if (!this.styles || !node) return;

            Alpine.store('builder').apply((doc) => {
                const next = ops.cloneDocument(doc);
                const entry = ops.findNode(next.nodes, node.id);
                if (entry) entry.node.styles = JSON.parse(JSON.stringify(this.styles));
                return next;
            });
        },
    });
}

import Alpine from 'alpinejs';
import ops from './document-ops';

/**
 * Canvas interaction.
 *
 * The canvas is a same-origin iframe rendering the *same* server-side
 * PageRenderer as the public page. The parent binds to its document on load to
 * add selection, hover and drag behaviour without the iframe needing any
 * builder knowledge.
 */
export function bindCanvas() {
    const frame = document.getElementById('builder-canvas');
    if (!frame) return;

    const onLoad = () => {
        const doc = frame.contentDocument;
        if (!doc) return;

        paintSelection(doc);
        bindNodes(frame, doc);
        bindDecorationDrag(frame, doc);
        bindDropzones(frame, doc);
    };

    frame.addEventListener('load', onLoad);

    // The iframe may already be loaded when this runs.
    if (frame.contentDocument?.readyState === 'complete') {
        onLoad();
    }

    // Repaint selection when it changes.
    Alpine.effect(() => {
        const id = Alpine.store('selection').selectedId;
        const hovered = Alpine.store('selection').hoveredId;

        const doc = frame.contentDocument;
        if (!doc) return;

        doc.querySelectorAll('.is-selected').forEach((el) => el.classList.remove('is-selected'));
        doc.querySelectorAll('.is-hovered').forEach((el) => el.classList.remove('is-hovered'));

        if (id) doc.querySelector(`[data-node-id="${cssEscape(id)}"]`)?.classList.add('is-selected');
        if (hovered) doc.querySelector(`[data-node-id="${cssEscape(hovered)}"]`)?.classList.add('is-hovered');
    });
}

function bindNodes(frame, doc) {
    doc.querySelectorAll('[data-node-id]').forEach((el) => {
        const id = el.dataset.nodeId;

        el.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            Alpine.store('selection').select(id);
        });

        el.addEventListener('mouseenter', () => Alpine.store('selection').hover(id));
        el.addEventListener('mouseleave', () => Alpine.store('selection').hover(null));

        // Keep links inert inside the editor.
        el.querySelectorAll('a').forEach((a) => {
            a.addEventListener('click', (event) => event.preventDefault());
        });

        // Inline text editing for nodes that expose a primary text prop.
        el.querySelectorAll('[data-edit-prop]').forEach((textEl) => {
            textEl.setAttribute('contenteditable', 'true');
            textEl.setAttribute('spellcheck', 'false');

            textEl.addEventListener('click', (event) => {
                event.stopPropagation();
                Alpine.store('selection').select(id);
            });

            textEl.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    textEl.blur();
                }
            });

            textEl.addEventListener('blur', () => {
                const prop = textEl.dataset.editProp;
                const value = textEl.textContent.replace(/\s+/g, ' ').trim();

                const entry = ops.findNode(Alpine.store('builder').document.nodes, id);
                if (!entry) return;

                if ((entry.node.props?.[prop] || '') === value) return;

                Alpine.store('builder').apply((document) => ops.setProp(document, id, prop, value));
                Alpine.store('builder').refreshCanvas();
            });
        });
    });
}

/**
 * Decorations drag freely: pointer movement maps to the x/y style of the
 * current device breakpoint.
 */
function bindDecorationDrag(frame, doc) {
    doc.querySelectorAll('[data-node-type="decoration"]').forEach((el) => {
        const id = el.dataset.nodeId;

        el.style.pointerEvents = 'auto';
        el.style.cursor = 'move';

        el.addEventListener('pointerdown', (event) => {
            if (event.button !== 0) return;

            const entry = ops.findNode(Alpine.store('builder').document.nodes, id);
            if (!entry || entry.node.locked) return;

            event.preventDefault();
            event.stopPropagation();
            Alpine.store('selection').select(id);

            const device = Alpine.store('ui').device;
            const start = { ...(entry.node.styles?.[device] || entry.node.styles?.desktop || {}) };
            const x0 = Number(start.x ?? 0);
            const y0 = Number(start.y ?? 0);
            const originX = event.clientX;
            const originY = event.clientY;

            const move = (moveEvent) => {
                const dx = moveEvent.clientX - originX;
                const dy = moveEvent.clientY - originY;

                Alpine.store('builder').apply((document) =>
                    ops.setStyle(document, id, device, 'x', Math.round(x0 + dx)),
                );
                Alpine.store('builder').apply((document) =>
                    ops.setStyle(document, id, device, 'y', Math.round(y0 + dy)),
                );

                // Move the live element too, so dragging feels immediate.
                el.style.left = `${x0 + dx}px`;
                el.style.top = `${y0 + dy}px`;
            };

            const up = () => {
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', up);
            };

            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
        });
    });
}

/**
 * Content nodes reorder via HTML5 drag; decorations are dragged with pointers.
 */
function bindDropzones(frame, doc) {
    doc.querySelectorAll('[data-node-id]').forEach((el) => {
        if (el.dataset.nodeType === 'decoration') return;

        el.setAttribute('draggable', 'true');

        el.addEventListener('dragstart', (event) => {
            event.dataTransfer.setData('text/plain', el.dataset.nodeId);
            event.dataTransfer.effectAllowed = 'move';
        });

        el.addEventListener('dragover', (event) => {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            el.classList.add('is-drop-target');
        });

        el.addEventListener('dragleave', () => el.classList.remove('is-drop-target'));

        el.addEventListener('drop', (event) => {
            event.preventDefault();
            event.stopPropagation();
            el.classList.remove('is-drop-target');

            const draggedId = event.dataTransfer.getData('text/plain');
            if (!draggedId || draggedId === el.dataset.nodeId) return;

            Alpine.store('builder').apply((document) =>
                ops.moveNode(document, draggedId, el.dataset.nodeId, null),
            );
            Alpine.store('builder').refreshCanvas();
        });
    });

    // Dropping on empty canvas space moves the node to the root.
    doc.body.addEventListener('dragover', (event) => event.preventDefault());
    doc.body.addEventListener('drop', (event) => {
        event.preventDefault();
        const draggedId = event.dataTransfer.getData('text/plain');
        if (!draggedId) return;

        Alpine.store('builder').apply((document) => ops.moveNode(document, draggedId, null, null));
        Alpine.store('builder').refreshCanvas();
    });
}

function paintSelection(doc) {
    if (doc.getElementById('builder-canvas-style')) return;

    const style = doc.createElement('style');
    style.id = 'builder-canvas-style';
    style.textContent = `
        [data-node-id] { cursor: pointer; }
        [data-node-id].is-hovered:not(.is-selected) { outline: 1px dashed rgba(59,130,246,.7); outline-offset: -1px; }
        [data-node-id].is-selected { outline: 2px solid rgba(59,130,246,.95); outline-offset: -2px; }
        [data-node-id].is-drop-target { outline: 2px dashed rgba(16,185,129,.9); outline-offset: -2px; }
        [data-node-type="decoration"].is-selected { outline: 2px solid rgba(59,130,246,.95); }
        [data-edit-prop][contenteditable]:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(59,130,246,.9);
            background: rgba(59,130,246,.06);
        }
    `;
    doc.head.appendChild(style);
}

function cssEscape(value) {
    return String(value).replace(/["\\]/g, '\\$&');
}

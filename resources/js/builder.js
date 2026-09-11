import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import { registerStores } from './builder/stores';
import { bindCanvas } from './builder/canvas';
import ops from './builder/document-ops';

/**
 * Builder entry point.
 *
 * Loaded ONLY by the builder view — the public invitation never pulls this in,
 * which is how the "public page stays lightweight" requirement is enforced
 * structurally rather than by convention.
 */
window.Alpine = Alpine;
// Exposed for the few inline Alpine expressions (layers drag-reorder) that
// need document operations directly.
window.NgundangOps = ops;
Alpine.plugin(intersect);

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('builder-root');
    if (!root) return;

    const config = JSON.parse(root.dataset.builder || '{}');

    registerStores(config);

    // Kick off the first canvas render.
    Alpine.store('builder').refreshCanvas();

    Alpine.start();

    bindCanvas();

    // Guard against losing unsaved work.
    window.addEventListener('beforeunload', (event) => {
        if (!Alpine.store('builder').dirty) return;
        event.preventDefault();
        event.returnValue = '';
    });

    // Editor keyboard shortcuts.
    window.addEventListener('keydown', (event) => {
        const meta = event.ctrlKey || event.metaKey;
        const target = event.target;
        const typing = target?.isContentEditable
            || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target?.tagName);

        if (meta && event.key.toLowerCase() === 's') {
            event.preventDefault();
            Alpine.store('builder').save();
            return;
        }

        if (typing) return;

        if (meta && event.key.toLowerCase() === 'z') {
            event.preventDefault();
            event.shiftKey ? Alpine.store('history').redo() : Alpine.store('history').undo();
            return;
        }

        if (meta && event.key.toLowerCase() === 'd') {
            event.preventDefault();
            Alpine.store('builder').duplicate();
            return;
        }

        if (meta && event.key.toLowerCase() === 'c') {
            const node = Alpine.store('builder').selected();
            if (node) Alpine.store('clipboard').copyNode(node);
            return;
        }

        if (meta && event.key.toLowerCase() === 'v') {
            Alpine.store('clipboard').pasteNode();
            return;
        }

        if (event.key === 'Delete' || event.key === 'Backspace') {
            const id = Alpine.store('selection').selectedId;
            if (id) {
                event.preventDefault();
                Alpine.store('builder').remove(id);
            }
        }

        if (event.key === 'Escape') {
            Alpine.store('selection').clear();
        }
    });
});

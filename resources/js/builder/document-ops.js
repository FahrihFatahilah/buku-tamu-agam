/**
 * Pure document operations.
 *
 * Every mutation returns a new object rather than mutating in place, so the
 * history store can keep cheap structural snapshots. No DOM, no Alpine — this
 * module is the testable core of the editor.
 */

/** Cryptographically-random-enough id for editor-time nodes. */
export function makeId(prefix = 'n') {
    const bytes = new Uint8Array(6);
    (window.crypto || {}).getRandomValues?.(bytes);
    let out = '';
    for (const b of bytes) out += b.toString(36);
    return `${prefix}_${out.slice(0, 10) || Math.random().toString(36).slice(2, 12)}`;
}

/** Depth-first walk yielding every node with its parent array. */
export function walk(nodes, parentArray = null, depth = 0) {
    const out = [];
    (nodes || []).forEach((node, index) => {
        out.push({ node, parentArray: nodes, index, depth });
        if (Array.isArray(node.children)) {
            out.push(...walk(node.children, node.children, depth + 1));
        }
    });
    return out;
}

/** Find a node by id anywhere in the tree. */
export function findNode(nodes, id) {
    return walk(nodes).find((entry) => entry.node.id === id) || null;
}

/** The array that directly contains the node with this id. */
export function findParentArray(nodes, id) {
    const entry = findNode(nodes, id);
    return entry ? entry.parentArray : null;
}

/** Ancestor chain (root-most first) for a node id. */
export function pathTo(nodes, id, trail = []) {
    for (const node of nodes || []) {
        const next = [...trail, node.id];
        if (node.id === id) return next;
        const found = pathTo(node.children || [], id, next);
        if (found) return found;
    }
    return null;
}

/** Insert a node into a container (or the root when parentId is null/unknown). */
export function insertNode(document, parentId, node, index = null) {
    const doc = cloneDocument(document);

    const target = parentId ? findNode(doc.nodes, parentId) : null;
    const list = target ? (target.node.children ||= []) : doc.nodes;

    if (index === null || index < 0 || index > list.length) {
        list.push(node);
    } else {
        list.splice(index, 0, node);
    }

    return doc;
}

/** Remove a node wherever it lives. */
export function removeNode(document, id) {
    const doc = cloneDocument(document);
    const parent = findParentArray(doc.nodes, id);

    if (parent) {
        const index = parent.findIndex((n) => n.id === id);
        if (index !== -1) parent.splice(index, 1);
    }

    return doc;
}

/**
 * Move an existing node to a new container/position.
 * Guards against dropping a node into its own subtree.
 */
export function moveNode(document, id, targetParentId, index = null) {
    const doc = cloneDocument(document);

    if (targetParentId && pathTo(doc.nodes, targetParentId)?.includes(id)) {
        return doc; // would create a cycle
    }

    const entry = findNode(doc.nodes, id);
    if (!entry) return doc;

    const [node] = entry.parentArray.splice(entry.index, 1);

    const target = targetParentId ? findNode(doc.nodes, targetParentId) : null;
    const list = target ? (target.node.children ||= []) : doc.nodes;

    if (index === null || index < 0 || index > list.length) {
        list.push(node);
    } else {
        list.splice(index, 0, node);
    }

    return doc;
}

/** Reorder within the same parent (used by the layers panel). */
export function reorderWithin(document, parentId, fromIndex, toIndex) {
    const doc = cloneDocument(document);
    const parent = parentId ? findNode(doc.nodes, parentId)?.node.children : doc.nodes;

    if (!Array.isArray(parent)) return doc;

    const [node] = parent.splice(fromIndex, 1);
    if (node) parent.splice(toIndex, 0, node);

    return doc;
}

/** Deep-clone a node, regenerating every id so identity stays unique. */
export function cloneNode(node) {
    return {
        ...JSON.parse(JSON.stringify(node)),
        id: makeId(node.type === 'decoration' ? 'deco' : 'n'),
        children: (node.children || []).map(cloneNode),
    };
}

/** Duplicate a node next to its original. */
export function duplicateNode(document, id) {
    const doc = cloneDocument(document);
    const entry = findNode(doc.nodes, id);

    if (!entry) return doc;

    entry.parentArray.splice(entry.index + 1, 0, cloneNode(entry.node));

    return doc;
}

/** All nodes flattened, for selectors and the layers panel. */
export function flatNodes(document) {
    return walk(document.nodes || []).map((entry) => ({
        id: entry.node.id,
        node: entry.node,
        depth: entry.depth,
        parentArray: entry.parentArray,
        index: entry.index,
    }));
}

/** Set a prop on a node. */
export function setProp(document, id, name, value) {
    return mapNode(document, id, (node) => {
        node.props = { ...(node.props || {}), [name]: value };
        return node;
    });
}

/** Set a style value for a specific breakpoint. */
export function setStyle(document, id, breakpoint, name, value) {
    return mapNode(document, id, (node) => {
        const styles = { ...(node.styles || {}) };
        const bp = { ...(styles[breakpoint] || {}) };

        if (value === null || value === undefined || value === '') {
            delete bp[name];
        } else {
            bp[name] = value;
        }

        if (Object.keys(bp).length) {
            styles[breakpoint] = bp;
        } else {
            delete styles[breakpoint];
        }

        node.styles = styles;
        return node;
    });
}

/** Set (or clear) one animation trigger on a node. */
export function setAnimation(document, id, trigger, config) {
    return mapNode(document, id, (node) => {
        const animation = { ...(node.animation || {}) };

        if (!config || !config.type) {
            delete animation[trigger];
        } else {
            animation[trigger] = config;
        }

        if (Object.keys(animation).length) {
            node.animation = animation;
        } else {
            delete node.animation;
        }

        return node;
    });
}

/** Toggle a flag on a node (locked / disabled / name). */
export function setNodeFlag(document, id, key, value) {
    return mapNode(document, id, (node) => {
        if (value === null || value === undefined || value === false || value === '') {
            delete node[key];
        } else {
            node[key] = value;
        }
        return node;
    });
}

/** Apply a transform to the node with this id, returning a new document. */
function mapNode(document, id, transform) {
    const doc = cloneDocument(document);

    const entry = findNode(doc.nodes, id);

    if (entry) {
        transform(entry.node);
    }

    return doc;
}

/** Overlays are a flat list, not a tree. */
export function addOverlay(document, type) {
    const doc = cloneDocument(document);
    doc.overlays = doc.overlays || [];
    doc.overlays.push({
        id: makeId('ov'),
        type,
        enabled: true,
        mobile: true,
        props: {},
        styles: {},
    });
    return doc;
}

export function removeOverlay(document, id) {
    const doc = cloneDocument(document);
    doc.overlays = (doc.overlays || []).filter((o) => o.id !== id);
    return doc;
}

export function updateOverlay(document, id, patch) {
    const doc = cloneDocument(document);
    doc.overlays = (doc.overlays || []).map((o) => (o.id === id ? { ...o, ...patch } : o));
    return doc;
}

export function updateTheme(document, patch) {
    const doc = cloneDocument(document);
    doc.theme = { ...(doc.theme || {}), ...patch };
    return doc;
}

export function cloneDocument(document) {
    return JSON.parse(JSON.stringify(document || { version: 2, theme: {}, nodes: [], overlays: [] }));
}

/** Count of enabled nodes, for the status bar. */
export function countNodes(document) {
    return flatNodes(document).length;
}

/**
 * A stable, cheap-ish signature used to decide whether a history checkpoint
 * is actually different from the last one.
 */
export function signature(document) {
    return JSON.stringify({
        nodes: document.nodes || [],
        overlays: document.overlays || [],
        theme: document.theme || {},
    });
}

export default {
    makeId,
    walk,
    findNode,
    findParentArray,
    pathTo,
    insertNode,
    removeNode,
    moveNode,
    reorderWithin,
    cloneNode,
    duplicateNode,
    flatNodes,
    setProp,
    setStyle,
    setAnimation,
    setNodeFlag,
    addOverlay,
    removeOverlay,
    updateOverlay,
    updateTheme,
    cloneDocument,
    countNodes,
    signature,
};

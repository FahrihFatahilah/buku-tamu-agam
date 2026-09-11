{{--
    Layers tree: nodes plus global overlays, with rename / hide / lock and
    drag-reorder within a parent.
--}}
<div class="flex flex-col min-h-0 h-full">
    <div class="flex-1 overflow-y-auto b-scroll min-h-0 py-1"
        x-data="{
            tree() {
                const out = [];
                const walk = (nodes, parentId, depth) => (nodes || []).forEach((node, index) => {
                    out.push({ node, parentId, depth, index });
                    walk(node.children || [], node.id, depth + 1);
                });
                walk($store.builder.document.nodes || [], null, 0);
                return out;
            },
            dragId: null,
            dropOn(targetId) {
                if (!this.dragId || this.dragId === targetId) return;
                $store.builder.apply((doc) => window.NgundangOps.moveNode(doc, this.dragId, targetId, null));
                $store.builder.refreshCanvas();
                this.dragId = null;
            }
        }">
        <template x-for="row in tree()" :key="row.node.id">
            <div draggable="true"
                @dragstart="dragId = row.node.id"
                @dragover.prevent
                @drop.prevent="dropOn(row.node.id)"
                @click="$store.selection.select(row.node.id)"
                class="group flex items-center gap-1.5 pr-2 py-1 cursor-pointer transition-colors"
                :class="$store.selection.selectedId === row.node.id ? 'bg-white/10' : 'hover:bg-white/5'"
                :style="`padding-left: ${8 + row.depth * 12}px`">

                <span class="text-[10px] text-stone-600 shrink-0 w-3">
                    <span x-show="row.node.children && row.node.children.length">▸</span>
                </span>

                <span class="flex-1 min-w-0 text-xs truncate"
                    :class="row.node.disabled ? 'text-stone-600 line-through' : 'text-stone-300'"
                    x-text="row.node.name || labelFor(row.node)"></span>

                <button type="button" @click.stop="$store.builder.toggleFlag(row.node.id, 'disabled')"
                    class="shrink-0 w-5 h-5 flex items-center justify-center rounded text-[10px] opacity-0 group-hover:opacity-100 transition-opacity"
                    :class="row.node.disabled ? 'text-amber-400 opacity-100' : 'text-stone-500 hover:text-stone-300'"
                    title="Tampilkan / sembunyikan">◉</button>

                <button type="button" @click.stop="$store.builder.toggleFlag(row.node.id, 'locked')"
                    class="shrink-0 w-5 h-5 flex items-center justify-center rounded text-[10px] opacity-0 group-hover:opacity-100 transition-opacity"
                    :class="row.node.locked ? 'text-amber-400 opacity-100' : 'text-stone-500 hover:text-stone-300'"
                    title="Kunci / buka">⌾</button>
            </div>
        </template>
    </div>

    {{-- Global overlays --}}
    <div class="shrink-0 border-t border-white/10" x-show="($store.builder.document.overlays || []).length">
        <p class="px-3 py-2 text-[10px] uppercase tracking-wider text-stone-500">Efek Latar</p>
        <template x-for="overlay in ($store.builder.document.overlays || [])" :key="overlay.id">
            <div class="flex items-center gap-2 px-3 py-1.5">
                <input type="checkbox" x-model="overlay.enabled"
                    @change="$store.builder.updateOverlay(overlay.id, { enabled: overlay.enabled }); $store.builder.scheduleRefresh(0)"
                    class="w-3 h-3 shrink-0">
                <span class="flex-1 text-xs truncate" x-text="overlayName(overlay.type)"></span>
                <button type="button" @click="$store.builder.removeOverlay(overlay.id); $store.builder.scheduleRefresh(0)"
                    class="text-stone-500 hover:text-red-400 text-xs">&times;</button>
            </div>
        </template>
    </div>

    <script>
        // These run from plain <script>, not an Alpine expression, so the
        // `$store` magic is unavailable here — reach the store via the
        // global Alpine instance instead.
        window.labelFor = function (node) {
            if (node.type === 'decoration') {
                return 'Dekorasi';
            }
            const item = (window.Alpine?.store('builder')?.registry?.widgets?.items || []).find((w) => w.type === node.type);
            return item ? item.name : node.type;
        };
        window.overlayName = function (type) {
            const item = (window.Alpine?.store('builder')?.registry?.overlays?.items || []).find((o) => o.type === type);
            return item ? item.name : type;
        };
    </script>
</div>

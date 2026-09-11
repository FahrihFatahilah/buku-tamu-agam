@php
    /**
     * Schema-driven inspector.
     *
     * Field sets are rendered once per registry definition and revealed based
     * on the current selection. Adding a widget to the registry therefore adds
     * its inspector UI automatically — no change to this file.
     *
     * Field sets render through a sub-view, so anything a control needs from
     * the page must be passed explicitly (sub-views do not inherit the parent's
     * local variables).
     */
    $renderFieldSet = function (array $fields, string $target) use ($giftMethods) {
        return view('builder.partials.fields', [
            'fields' => $fields,
            'target' => $target,
            'giftMethods' => $giftMethods,
        ])->render();
    };
@endphp

<div class="flex flex-col min-h-0 h-full text-stone-300">
    <div class="shrink-0 border-b border-white/10">
        <div x-show="$store.selection.selectedId" x-cloak class="px-3 py-2">
            <p class="text-xs text-stone-200 truncate" x-text="selectionLabel()"></p>
            <p class="text-[10px] text-stone-600 font-mono truncate" x-text="$store.selection.selectedId"></p>
        </div>
        <p x-show="!$store.selection.selectedId" class="px-3 py-2 text-[11px] text-stone-500">
            Pilih elemen di kanvas untuk mengatur propertinya. Tanpa pilihan, pengaturan tema global yang tampil.
        </p>

        <div x-show="$store.selection.selectedId" x-cloak class="flex border-t border-white/5">
            <template x-for="tab in [['content','Konten'],['style','Gaya'],['animation','Animasi']]" :key="tab[0]">
                <button type="button" @click="$store.ui.inspectorTab = tab[0]"
                    class="flex-1 py-2 text-[11px] transition-colors"
                    :class="$store.ui.inspectorTab === tab[0]
                        ? 'text-white border-b-2 border-white'
                        : 'text-stone-500 hover:text-stone-300'"
                    x-text="tab[1]"></button>
            </template>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto b-scroll min-h-0 p-3 space-y-3">

        {{-- ── Nothing selected: global theme ───────────────────────────── --}}
        <div x-show="!$store.selection.selectedId" x-cloak class="space-y-4">
            <div>
                <p class="text-[10px] uppercase tracking-wider text-stone-500 mb-2">Warna Tema</p>
                <div class="space-y-2">
                    <template x-for="token in ['primary','secondary','accent','background','text']" :key="token">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-stone-400 w-20 shrink-0 capitalize" x-text="token"></span>
                            <input type="color"
                                :value="$store.builder.document.theme?.colors?.[token] || '#000000'"
                                @input="$store.builder.setTheme({ colors: Object.assign({}, $store.builder.document.theme?.colors || {}, { [token]: $event.target.value }) }); $store.builder.scheduleRefresh()"
                                class="w-7 h-7 shrink-0 border border-white/10 rounded cursor-pointer bg-transparent">
                            <input type="text"
                                class="flex-1 min-w-0 bg-white/5 border border-white/10 px-2 py-1 text-[11px] font-mono rounded focus:outline-none focus:border-white/30"
                                :value="$store.builder.document.theme?.colors?.[token] || ''"
                                @input.debounce.400ms="$store.builder.setTheme({ colors: Object.assign({}, $store.builder.document.theme?.colors || {}, { [token]: $event.target.value }) }); $store.builder.scheduleRefresh()">
                        </div>
                    </template>
                </div>
                <p class="text-[10px] text-stone-600 mt-2">
                    Di inspector, isi warna dengan nama token (mis. <code>accent</code>) untuk mengikuti tema.
                </p>
            </div>

            <div class="pt-3 border-t border-white/10 space-y-2">
                <p class="text-[10px] uppercase tracking-wider text-stone-500">Tipografi</p>
                <label class="block">
                    <span class="block text-[11px] text-stone-400 mb-1">Font Judul</span>
                    <input type="text" :value="$store.builder.document.theme?.typography?.headingFont || ''"
                        @input.debounce.500ms="$store.builder.setTheme({ typography: Object.assign({}, $store.builder.document.theme?.typography || {}, { headingFont: $event.target.value }) }); $store.builder.scheduleRefresh()"
                        class="w-full bg-white/5 border border-white/10 px-2 py-1.5 text-xs rounded focus:outline-none focus:border-white/30">
                </label>
                <label class="block">
                    <span class="block text-[11px] text-stone-400 mb-1">Font Isi</span>
                    <input type="text" :value="$store.builder.document.theme?.typography?.bodyFont || ''"
                        @input.debounce.500ms="$store.builder.setTheme({ typography: Object.assign({}, $store.builder.document.theme?.typography || {}, { bodyFont: $event.target.value }) }); $store.builder.scheduleRefresh()"
                        class="w-full bg-white/5 border border-white/10 px-2 py-1.5 text-xs rounded focus:outline-none focus:border-white/30">
                </label>
            </div>

            <div class="pt-3 border-t border-white/10">
                <label class="block">
                    <span class="block text-[11px] text-stone-400 mb-1">Radius Sudut (px)</span>
                    <input type="number" min="0" max="80" :value="$store.builder.document.theme?.radius ?? 4"
                        @input.debounce.400ms="$store.builder.setTheme({ radius: Number($event.target.value) }); $store.builder.scheduleRefresh()"
                        class="w-full bg-white/5 border border-white/10 px-2 py-1.5 text-xs rounded focus:outline-none focus:border-white/30">
                </label>
            </div>
        </div>

        {{-- ── Widget field sets, one per registered type ───────────────── --}}
        @foreach($registry['widgets']['items'] as $widget)
        @php
            $tabs = [
                'content' => $widget['inspector']['content'] ?? [],
                'style' => $widget['inspector']['style'] ?? [],
            ];
        @endphp
        <div x-show="$store.selection.selectedId && nodeType() === '{{ $widget['type'] }}'" x-cloak class="space-y-3">
            @foreach($tabs as $tab => $fields)
            <div x-show="$store.ui.inspectorTab === '{{ $tab }}'" x-cloak class="space-y-3">
                @if($fields)
                {!! $renderFieldSet($fields, $tab === 'style' ? 'styles' : 'props') !!}
                @else
                <p class="text-[11px] text-stone-600">Tidak ada pengaturan pada tab ini.</p>
                @endif
            </div>
            @endforeach

            <div x-show="$store.ui.inspectorTab === 'animation'" x-cloak>
                @include('builder.panels.animation-fields')
            </div>
        </div>
        @endforeach

        {{-- ── Decorations ──────────────────────────────────────────────── --}}
        <div x-show="$store.selection.selectedId && nodeType() === 'decoration'" x-cloak class="space-y-3">
            <div x-show="$store.ui.inspectorTab === 'content'" x-cloak class="space-y-3">
                {!! $renderFieldSet($registry['decorations']['propFields'] ?? [], 'props') !!}
            </div>
            <div x-show="$store.ui.inspectorTab === 'style'" x-cloak class="space-y-3">
                {!! $renderFieldSet($registry['decorations']['styleFields'] ?? [], 'styles') !!}
            </div>
            <div x-show="$store.ui.inspectorTab === 'animation'" x-cloak>
                @include('builder.panels.animation-fields')
            </div>
        </div>

        {{-- ── Overlays (selected from the layers panel) ────────────────── --}}
        @foreach($registry['overlays']['items'] as $overlay)
        <div x-show="$store.selection.selectedId === 'overlay:{{ $overlay['type'] }}'" x-cloak class="space-y-3">
            <p class="text-xs text-stone-200">{{ $overlay['name'] }}</p>
            {!! $renderFieldSet($overlay['inspector'] ?? [], 'overlay-props') !!}

            <label class="flex items-center justify-between py-1 cursor-pointer">
                <span class="text-xs text-stone-300">Tampilkan</span>
                <input type="checkbox" class="sr-only peer"
                    x-effect="$el.checked = !!overlayField('enabled')"
                    @change="updateOverlay({ enabled: $event.target.checked })">
                <span class="relative w-8 h-4 rounded-full bg-white/15 peer-checked:bg-emerald-500 transition-colors
                    after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-3 after:h-3 after:rounded-full
                    after:bg-white after:transition-transform peer-checked:after:translate-x-4"></span>
            </label>

            <label class="flex items-center justify-between py-1 cursor-pointer">
                <span class="text-xs text-stone-300">Tampil di ponsel</span>
                <input type="checkbox" class="sr-only peer"
                    x-effect="$el.checked = !!overlayField('mobile')"
                    @change="updateOverlay({ mobile: $event.target.checked })">
                <span class="relative w-8 h-4 rounded-full bg-white/15 peer-checked:bg-emerald-500 transition-colors
                    after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-3 after:h-3 after:rounded-full
                    after:bg-white after:transition-transform peer-checked:after:translate-x-4"></span>
            </label>
        </div>
        @endforeach
    </div>
</div>

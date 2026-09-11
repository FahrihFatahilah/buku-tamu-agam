{{-- Element palette: registry-driven, searchable. --}}
<div class="flex flex-col min-h-0 h-full">
    <div class="shrink-0 p-2 border-b border-white/10">
        <input type="search" x-model="$store.ui.search" placeholder="Cari elemen…"
            class="w-full bg-white/5 border border-white/10 px-2.5 py-2 text-xs text-stone-100 placeholder-stone-500 rounded focus:outline-none focus:border-white/30">
    </div>

    <div class="flex-1 overflow-y-auto b-scroll min-h-0">
        <template x-for="group in $store.ui.filteredGroups" :key="group.key">
            <div class="border-b border-white/5">
                <p class="px-3 py-2 text-[10px] uppercase tracking-wider text-stone-500" x-text="group.label"></p>
                <div class="pb-1">
                    <template x-for="item in group.items" :key="item.type">
                        <button type="button"
                            @click="$store.builder.addWidget(item.type)"
                            class="w-full text-left px-3 py-1.5 text-xs text-stone-300 hover:bg-white/5 hover:text-white transition-colors flex items-center gap-2"
                            :title="item.description || ''">
                            <span class="w-1.5 h-1.5 rounded-full bg-stone-600 shrink-0"></span>
                            <span class="truncate" x-text="item.name"></span>
                        </button>
                    </template>
                </div>
            </div>
        </template>

        {{-- Decorations --}}
        <div class="border-b border-white/5">
            <p class="px-3 py-2 text-[10px] uppercase tracking-wider text-stone-500">Dekorasi</p>
            <div class="grid grid-cols-2 gap-1.5 px-3 pb-3">
                @foreach($registry['decorations']['items'] as $decoration)
                <button type="button"
                    @click="$store.builder.addDecoration('{{ $decoration['asset'] }}')"
                    class="flex flex-col items-center gap-1 py-2 border border-white/10 rounded hover:border-white/30 hover:bg-white/5 transition-colors"
                    title="{{ $decoration['name'] }}">
                    <img src="{{ asset('builder/decorations/' . $decoration['asset'] . '.svg') }}" alt=""
                        class="w-6 h-6 opacity-70" style="filter: invert(1);">
                    <span class="text-[10px] text-stone-400">{{ $decoration['name'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Overlays --}}
        <div>
            <p class="px-3 py-2 text-[10px] uppercase tracking-wider text-stone-500">Efek Latar</p>
            <div class="pb-3">
                @foreach($registry['overlays']['items'] as $overlay)
                <button type="button"
                    @click="$store.builder.addOverlay('{{ $overlay['type'] }}')"
                    class="w-full text-left px-3 py-1.5 text-xs text-stone-300 hover:bg-white/5 hover:text-white transition-colors flex items-center gap-2">
                    <span class="truncate">{{ $overlay['name'] }}</span>
                    @if($overlay['animated'])
                    <span class="ml-auto text-[9px] text-stone-600 border border-white/10 px-1 rounded shrink-0">animasi</span>
                    @endif
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

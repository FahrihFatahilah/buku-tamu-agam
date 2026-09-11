{{-- Animation controls for the current selection. --}}
<div class="space-y-4">
    <p class="text-[10px] text-stone-600">
        Animasi dijalankan dengan CSS + IntersectionObserver, dan otomatis nonaktif
        saat pengunjung mengaktifkan <em>reduced motion</em>.
    </p>

    <template x-for="trigger in [['entrance','Muncul'],['continuous','Terus-menerus'],['scroll','Saat Scroll']]" :key="trigger[0]">
        <div class="border border-white/10 rounded p-2 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-stone-300" x-text="trigger[1]"></span>
                <button type="button" class="text-[10px] text-stone-500 hover:text-stone-300"
                    x-text="(($store.builder.nodeAnimation(trigger[0]) || {}).type) ? 'Hapus' : 'Tambah'"
                    @click="(($store.builder.nodeAnimation(trigger[0]) || {}).type)
                        ? $store.builder.clearAnimation(trigger[0])
                        : $store.builder.setAnimationType(trigger[0], $store.builder.defaultAnimationFor(trigger[0]))"></button>
            </div>

            <template x-if="($store.builder.nodeAnimation(trigger[0]) || {}).type">
                <div class="space-y-2">
                    <select class="w-full bg-white/5 border border-white/10 px-2 py-1.5 text-xs rounded focus:outline-none focus:border-white/30"
                        :value="$store.builder.nodeAnimation(trigger[0]).type"
                        @change="$store.builder.setAnimationType(trigger[0], $event.target.value)">
                        <template x-for="option in $store.builder.animationOptionsFor(trigger[0])" :key="option.type">
                            <option :value="option.type" x-text="option.name" class="bg-stone-800"></option>
                        </template>
                    </select>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="block">
                            <span class="block text-[10px] text-stone-500 mb-0.5">Durasi (ms)</span>
                            <input type="number" min="0" max="20000" step="50"
                                :value="$store.builder.nodeAnimation(trigger[0]).duration ?? 700"
                                @input.debounce.400ms="$store.builder.patchAnimation(trigger[0], { duration: Number($event.target.value) })"
                                class="w-full bg-white/5 border border-white/10 px-2 py-1 text-xs rounded focus:outline-none focus:border-white/30">
                        </label>
                        <label class="block" x-show="trigger[0] === 'entrance'">
                            <span class="block text-[10px] text-stone-500 mb-0.5">Delay (ms)</span>
                            <input type="number" min="0" max="10000" step="50"
                                :value="$store.builder.nodeAnimation(trigger[0]).delay ?? 0"
                                @input.debounce.400ms="$store.builder.patchAnimation(trigger[0], { delay: Number($event.target.value) })"
                                class="w-full bg-white/5 border border-white/10 px-2 py-1 text-xs rounded focus:outline-none focus:border-white/30">
                        </label>
                    </div>

                    <label class="flex items-center justify-between text-[11px] text-stone-400 cursor-pointer"
                        x-show="trigger[0] === 'entrance'">
                        <span>Ulangi saat masuk layar lagi</span>
                        <input type="checkbox"
                            :checked="$store.builder.nodeAnimation(trigger[0]).repeat === 'loop'"
                            @change="$store.builder.patchAnimation(trigger[0], { repeat: $event.target.checked ? 'loop' : null })">
                    </label>
                </div>
            </template>
        </div>
    </template>
</div>

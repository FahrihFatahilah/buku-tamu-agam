<div x-data="{ get s() { return $store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}') || {}; } }">
    <span class="block text-[11px] text-stone-400 mb-1">{{ $field['label'] ?? $field['name'] }}</span>
    <div class="grid grid-cols-4 gap-1.5">
        @foreach(['x' => 'X', 'y' => 'Y', 'blur' => 'Blur', 'spread' => 'Sebar'] as $key => $label)
        <div>
            <span class="block text-[9px] text-stone-600 mb-0.5 text-center">{{ $label }}</span>
            <input type="number" step="1" :value="s.{{ $key }} ?? ''"
                @input.debounce.400ms="
                    const next = Object.assign({ x: 0, y: 0, blur: 0, spread: 0 }, s);
                    next['{{ $key }}'] = $event.target.value === '' ? 0 : Number($event.target.value);
                    $store.builder.fieldWrite('{{ $field['name'] }}', next, '{{ $target }}')
                "
                class="w-full bg-white/5 border border-white/10 px-1 py-1.5 text-[11px] text-center text-stone-100 rounded focus:outline-none focus:border-white/30">
        </div>
        @endforeach
    </div>
    <div class="flex items-center gap-2 mt-1.5">
        <input type="color" title="Warna bayangan" :value="(s.color || '#000000').slice(0, 7)"
            @input="$store.builder.fieldWrite('{{ $field['name'] }}', Object.assign({ x: 0, y: 8, blur: 24, spread: 0 }, s, { color: $event.target.value }), '{{ $target }}')"
            class="w-7 h-7 shrink-0 border border-white/10 rounded cursor-pointer bg-transparent">
        <label class="flex items-center gap-1.5 text-[11px] text-stone-400 cursor-pointer">
            <input type="checkbox" :checked="!!s.inset"
                @change="$store.builder.fieldWrite('{{ $field['name'] }}', Object.assign({ x: 0, y: 8, blur: 24, spread: 0 }, s, { inset: $event.target.checked }), '{{ $target }}')">
            Dalam
        </label>
        <button type="button"
            @click="$store.builder.fieldWrite('{{ $field['name'] }}', null, '{{ $target }}')"
            class="ml-auto text-stone-500 hover:text-stone-300 text-[11px]">Reset</button>
    </div>
</div>

<div x-data="{ get g() { return $store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}') || {}; } }">
    <span class="block text-[11px] text-stone-400 mb-1">{{ $field['label'] ?? $field['name'] }}</span>
    <div class="space-y-1.5">
        <div class="flex items-center gap-2">
            <span class="text-[10px] text-stone-500 w-8 shrink-0">Dari</span>
            <input type="color" :value="g.from || '#000000'"
                @input="$store.builder.fieldWrite('{{ $field['name'] }}', { from: $event.target.value, to: g.to || '#00000000', direction: g.direction || 'to-bottom' }, '{{ $target }}')"
                class="w-7 h-7 shrink-0 border border-white/10 rounded cursor-pointer bg-transparent">
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[10px] text-stone-500 w-8 shrink-0">Ke</span>
            <input type="color" :value="(g.to || '#ffffff').slice(0, 7)"
                @input="$store.builder.fieldWrite('{{ $field['name'] }}', { from: g.from || '#000000', to: $event.target.value, direction: g.direction || 'to-bottom' }, '{{ $target }}')"
                class="w-7 h-7 shrink-0 border border-white/10 rounded cursor-pointer bg-transparent">
        </div>
        <select :value="g.direction || 'to-bottom'"
            @change="$store.builder.fieldWrite('{{ $field['name'] }}', { from: g.from || '#000000', to: g.to || '#00000000', direction: $event.target.value }, '{{ $target }}')"
            class="w-full bg-white/5 border border-white/10 px-2 py-1.5 text-[11px] text-stone-100 rounded focus:outline-none focus:border-white/30">
            <option value="to-top" class="bg-stone-800">Ke atas</option>
            <option value="to-bottom" class="bg-stone-800">Ke bawah</option>
            <option value="to-left" class="bg-stone-800">Ke kiri</option>
            <option value="to-right" class="bg-stone-800">Ke kanan</option>
        </select>
    </div>
</div>

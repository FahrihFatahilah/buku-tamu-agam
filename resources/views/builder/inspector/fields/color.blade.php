<label class="block">
    <span class="block text-[11px] text-stone-400 mb-1">{{ $field['label'] ?? $field['name'] }}</span>
    <div class="flex items-center gap-2">
        <input type="color"
            x-effect="$el.value = $store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}') || '#000000'"
            @input="$store.builder.fieldWrite('{{ $field['name'] }}', $event.target.value, '{{ $target }}')"
            class="w-8 h-8 shrink-0 border border-white/10 rounded cursor-pointer bg-transparent">
        <input type="text" placeholder="token atau #hex"
            :value="$store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}')"
            @input.debounce.400ms="$store.builder.fieldWrite('{{ $field['name'] }}', $event.target.value, '{{ $target }}')"
            class="flex-1 min-w-0 bg-white/5 border border-white/10 px-2 py-1.5 text-xs font-mono text-stone-100 rounded focus:outline-none focus:border-white/30">
        <button type="button" title="Kosongkan"
            @click="$store.builder.fieldWrite('{{ $field['name'] }}', '', '{{ $target }}')"
            class="shrink-0 text-stone-500 hover:text-stone-300 text-xs px-1.5">&times;</button>
    </div>
</label>

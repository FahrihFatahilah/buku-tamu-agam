<label class="block">
    <span class="block text-[11px] text-stone-400 mb-1">{{ $field['label'] ?? $field['name'] }}</span>
    <input type="text"
        :value="$store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}')"
        @input.debounce.400ms="$store.builder.fieldWrite('{{ $field['name'] }}', $event.target.value, '{{ $target }}')"
        class="w-full bg-white/5 border border-white/10 px-2.5 py-2 text-sm text-stone-100 rounded focus:outline-none focus:border-white/30">
</label>

<label class="flex items-center justify-between py-1 cursor-pointer">
    <span class="text-xs text-stone-300">{{ $field['label'] ?? $field['name'] }}</span>
    <input type="checkbox" class="sr-only peer"
        x-effect="$el.checked = !!$store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}')"
        @change="$store.builder.fieldWrite('{{ $field['name'] }}', $event.target.checked, '{{ $target }}')">
    <span class="relative w-8 h-4 rounded-full bg-white/15 peer-checked:bg-emerald-500 transition-colors
        after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-3 after:h-3 after:rounded-full
        after:bg-white after:transition-transform peer-checked:after:translate-x-4"></span>
</label>

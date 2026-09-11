<label class="block">
    <span class="flex items-center justify-between text-[11px] text-stone-400 mb-1">
        <span>{{ $field['label'] ?? $field['name'] }}</span>
        <span class="text-stone-500 tabular-nums"
            x-text="$store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}')"></span>
    </span>
    <input type="range"
        min="{{ $field['min'] ?? 0 }}" max="{{ $field['max'] ?? 100 }}" step="{{ $field['step'] ?? 1 }}"
        x-effect="$el.value = $store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}')"
        @input="$store.builder.fieldWrite('{{ $field['name'] }}', Number($event.target.value), '{{ $target }}')"
        class="b-range w-full">
</label>

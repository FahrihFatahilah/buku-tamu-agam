<label class="block">
    <span class="block text-[11px] text-stone-400 mb-1">{{ $field['label'] ?? $field['name'] }}</span>
    <select
        x-effect="$el.value = $store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}') ?? ''"
        @change="$store.builder.fieldWrite('{{ $field['name'] }}', $event.target.value === '' ? null : Number($event.target.value), '{{ $target }}')"
        class="w-full bg-white/5 border border-white/10 px-2 py-2 text-xs text-stone-100 rounded focus:outline-none focus:border-white/30">
        <option value="" class="bg-stone-800">— pilih rekening —</option>
        @foreach($giftMethods as $method)
        <option value="{{ $method->id }}" class="bg-stone-800">{{ $method->label }}</option>
        @endforeach
    </select>
</label>

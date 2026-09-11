@php $sides = ['top' => 'Atas', 'right' => 'Kanan', 'bottom' => 'Bawah', 'left' => 'Kiri']; @endphp
<div>
    <span class="block text-[11px] text-stone-400 mb-1">{{ $field['label'] ?? $field['name'] }}</span>
    <div class="grid grid-cols-4 gap-1.5">
        @foreach($sides as $side => $label)
        <div>
            <span class="block text-[9px] text-stone-600 mb-0.5 text-center">{{ $label }}</span>
            <input type="number" min="-400" max="800" step="1" placeholder="0"
                x-effect="
                    const box = $store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}');
                    $el.value = (box && typeof box === 'object' && '{{ $side }}' in box) ? box.{{ $side }} : '';
                "
                @input.debounce.400ms="
                    const current = $store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}');
                    const box = Object.assign({ top: 0, right: 0, bottom: 0, left: 0 }, (current && typeof current === 'object') ? current : {});
                    box['{{ $side }}'] = $event.target.value === '' ? 0 : Number($event.target.value);
                    $store.builder.fieldWrite('{{ $field['name'] }}', box, '{{ $target }}')
                "
                class="w-full bg-white/5 border border-white/10 px-1 py-1.5 text-[11px] text-center text-stone-100 rounded focus:outline-none focus:border-white/30">
        </div>
        @endforeach
    </div>
</div>

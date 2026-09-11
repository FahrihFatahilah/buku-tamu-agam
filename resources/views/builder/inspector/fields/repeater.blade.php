@php
    $name = $field['name'];
    $subFields = $field['fields'] ?? [];
@endphp
<div x-data="{ items: [] }"
    x-effect="items = JSON.parse(JSON.stringify($store.builder.fieldValue('{{ $name }}', '{{ $target }}') || []))"
    class="space-y-2">
    <span class="block text-[11px] text-stone-400">{{ $field['label'] ?? $name }}</span>

    <template x-for="(item, index) in items" :key="index">
        <div class="border border-white/10 rounded p-2 space-y-1.5 bg-white/[0.02]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] text-stone-500" x-text="'#' + (index + 1)"></span>
                <button type="button" class="text-stone-500 hover:text-red-400 text-xs px-1" title="Hapus"
                    @click="items.splice(index, 1); $store.builder.fieldWrite('{{ $name }}', JSON.parse(JSON.stringify(items)), '{{ $target }}')">&times;</button>
            </div>

            @foreach($subFields as $sub)
            <label class="block">
                <span class="block text-[10px] text-stone-500 mb-0.5">{{ $sub['label'] ?? $sub['name'] }}</span>
                @if(($sub['type'] ?? 'text') === 'textarea')
                <textarea rows="2" x-model="item.{{ $sub['name'] }}"
                    @input.debounce.400ms="$store.builder.fieldWrite('{{ $name }}', JSON.parse(JSON.stringify(items)), '{{ $target }}')"
                    class="w-full bg-white/5 border border-white/10 px-2 py-1 text-xs text-stone-100 rounded focus:outline-none focus:border-white/30 resize-none"></textarea>
                @else
                <input type="text" x-model="item.{{ $sub['name'] }}"
                    @input.debounce.400ms="$store.builder.fieldWrite('{{ $name }}', JSON.parse(JSON.stringify(items)), '{{ $target }}')"
                    class="w-full bg-white/5 border border-white/10 px-2 py-1 text-xs text-stone-100 rounded focus:outline-none focus:border-white/30">
                @endif
            </label>
            @endforeach
        </div>
    </template>

    <button type="button"
        class="w-full text-[11px] text-stone-400 hover:text-stone-200 border border-dashed border-white/15 rounded py-1.5"
        @click="
            const blank = {};
            @foreach($subFields as $sub) blank['{{ $sub['name'] }}'] = ''; @endforeach
            items.push(blank);
            $store.builder.fieldWrite('{{ $name }}', JSON.parse(JSON.stringify(items)), '{{ $target }}')
        ">
        + Tambah baris
    </button>
</div>

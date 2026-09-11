<div x-data="{ value: '' }"
    x-effect="value = $store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}') || ''">
    <span class="block text-[11px] text-stone-400 mb-1">{{ $field['label'] ?? $field['name'] }}</span>
    <div class="flex items-center gap-1 mb-1">
        <button type="button" @click.prevent="document.execCommand('bold'); $nextTick(() => $store.builder.fieldWrite('{{ $field['name'] }}', $refs.editor.innerHTML, '{{ $target }}'))"
            class="px-1.5 py-0.5 text-[11px] bg-white/5 border border-white/10 rounded hover:bg-white/10"><b>B</b></button>
        <button type="button" @click.prevent="document.execCommand('italic'); $nextTick(() => $store.builder.fieldWrite('{{ $field['name'] }}', $refs.editor.innerHTML, '{{ $target }}'))"
            class="px-1.5 py-0.5 text-[11px] bg-white/5 border border-white/10 rounded hover:bg-white/10"><i>I</i></button>
        <button type="button" @click.prevent="document.execCommand('underline'); $nextTick(() => $store.builder.fieldWrite('{{ $field['name'] }}', $refs.editor.innerHTML, '{{ $target }}'))"
            class="px-1.5 py-0.5 text-[11px] bg-white/5 border border-white/10 rounded hover:bg-white/10"><u>U</u></button>
    </div>
    <div x-ref="editor" contenteditable="true" x-html="value"
        @input.debounce.400ms="$store.builder.fieldWrite('{{ $field['name'] }}', $refs.editor.innerHTML, '{{ $target }}')"
        class="min-h-[72px] bg-white/5 border border-white/10 px-2 py-2 text-xs text-stone-100 rounded focus:outline-none focus:border-white/30"></div>
</div>

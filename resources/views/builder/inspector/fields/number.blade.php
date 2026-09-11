@php
    $min = $field['min'] ?? null;
    $max = $field['max'] ?? null;
    $unit = $field['unit'] ?? '';
@endphp
<label class="block">
    <span class="block text-[11px] text-stone-400 mb-1">
        {{ $field['label'] ?? $field['name'] }}@if($unit !== '') <span class="text-stone-600">({{ $unit }})</span>@endif
    </span>
    <input type="number" step="any"
        @if($min !== null) min="{{ $min }}" @endif
        @if($max !== null) max="{{ $max }}" @endif
        x-effect="$el.value = $store.builder.fieldValue('{{ $field['name'] }}', '{{ $target }}') ?? ''"
        @input.debounce.400ms="
            const raw = $event.target.value;
            $store.builder.fieldWrite('{{ $field['name'] }}', raw === '' ? null : Number(raw), '{{ $target }}')
        "
        class="w-full bg-white/5 border border-white/10 px-2.5 py-2 text-sm text-stone-100 rounded focus:outline-none focus:border-white/30">
</label>

@php
    /** Shared gift method card, so `gift` and `bank-account` stay consistent. */
    $label = $method->label ?? '';
    $type = $method->type ?? 'bank_transfer';
@endphp
<div class="p-5" style="border: 1px solid color-mix(in srgb, currentColor 15%, transparent);">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <p class="font-medium text-sm">{{ $label }}</p>

            @if($type === 'bank_transfer')
            @if($method->bank_name)
            <p class="text-xs opacity-60 mt-1">{{ $method->bank_name }}</p>
            @endif
            @if($method->account_number)
            <p class="font-mono text-base mt-1" style="color: var(--n-primary, #7C3238);">{{ $method->account_number }}</p>
            @endif
            @if($method->account_holder)
            <p class="text-xs opacity-60 mt-0.5">a.n. {{ $method->account_holder }}</p>
            @endif
            @elseif($type === 'qris' && $method->image)
            <div class="mt-3">
                <img src="{{ Storage::url($method->image) }}" alt="QRIS {{ $method->merchant_name }}"
                    class="w-32 h-32 object-contain" loading="lazy">
            </div>
            @elseif($method->merchant_name)
            <p class="text-sm opacity-70 mt-1">{{ $method->merchant_name }}</p>
            @endif

            @if($method->description)
            <p class="text-xs opacity-60 mt-2">{{ $method->description }}</p>
            @endif
        </div>

        @if($showCopy && $type === 'bank_transfer' && $method->account_number)
        <button type="button"
            onclick="navigator.clipboard.writeText('{{ $method->account_number }}')"
            class="shrink-0 px-3 py-1.5 text-xs"
            style="border: 1px solid color-mix(in srgb, currentColor 20%, transparent);">
            Salin
        </button>
        @endif
    </div>
</div>

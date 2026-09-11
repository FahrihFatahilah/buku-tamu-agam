@php
    $p = $node['props'] ?? [];
    $giftId = $p['giftId'] ?? null;
    $showCopy = $p['showCopy'] ?? true;

    // Reference a specific gift method by id; never duplicate its data.
    $method = $giftId ? $giftMethods->firstWhere('id', (int) $giftId) : $giftMethods->first();
@endphp
@if($method)
<div class="n-inner">
    @include('invitation.widgets._gift-card', ['method' => $method, 'showCopy' => $showCopy])
</div>
@endif
